<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\OrderDraft;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Cari user berdasarkan nomor telepon atau ID, atau buat akun baru otomatis jika belum terdaftar.
     */
    public function findOrCreateUserByPhone(string $phone, ?string $name = null): User
    {
        // Jika format ID user website (contoh: user_12)
        if (str_starts_with($phone, 'user_')) {
            $userId = (int) substr($phone, 5);
            $user = User::find($userId);
            if ($user) {
                return $user;
            }
        }

        // Normalisasi nomor telepon
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $user = User::where('no_telepon', $phone)
            ->orWhere('no_telepon', $cleanPhone)
            ->first();

        if ($user) {
            return $user;
        }

        // Buat akun pelanggan baru otomatis
        $namaUser = $name ?: 'Pelanggan WA '.substr($cleanPhone, -4);
        $email = 'wa_'.$cleanPhone.'@tokosembwko.local';

        // Pastikan email unik jika ada collision
        if (User::where('email', $email)->exists()) {
            $email = 'wa_'.$cleanPhone.'_'.Str::random(4).'@tokosembwko.local';
        }

        return User::create([
            'nama_user' => $namaUser,
            'no_telepon' => $phone,
            'email' => $email,
            'password' => Hash::make(Str::random(16)),
            'role' => 'user',
        ]);
    }

    /**
     * Dapatkan draft pesanan aktif untuk nomor telepon tertentu.
     */
    public function getActiveDraft(string $phone): ?OrderDraft
    {
        $draft = OrderDraft::where('sender_phone', $phone)
            ->where('status', 'menunggu_konfirmasi')
            ->latest('id')
            ->first();

        if ($draft && now()->greaterThan($draft->expires_at)) {
            $draft->update(['status' => 'kedaluwarsa']);

            return null;
        }

        return $draft;
    }

    /**
     * Buat atau perbarui draft pesanan baru dari pesan WhatsApp.
     *
     * @param  array<array{id_produk: int, jumlah: int}>  $itemsRequested
     * @return array{success: bool, draft: ?OrderDraft, pesan: string, ringkasan_teks: ?string}
     */
    public function createDraft(string $phone, array $itemsRequested, ?string $senderName = null): array
    {
        if (empty($itemsRequested)) {
            return [
                'success' => false,
                'draft' => null,
                'pesan' => 'Daftar item pesanan kosong.',
                'ringkasan_teks' => null,
            ];
        }

        $validatedItems = [];
        $totalHarga = 0;

        foreach ($itemsRequested as $item) {
            $idProduk = (int) ($item['id_produk'] ?? 0);
            $jumlah = (int) ($item['jumlah'] ?? 1);

            if ($jumlah <= 0) {
                continue;
            }

            $produk = Produk::find($idProduk);
            if (! $produk || ! $produk->status) {
                return [
                    'success' => false,
                    'draft' => null,
                    'pesan' => 'Salah satu produk yang dipilih sudah tidak tersedia di katalog.',
                    'ringkasan_teks' => null,
                ];
            }

            if ($produk->stok < $jumlah) {
                return [
                    'success' => false,
                    'draft' => null,
                    'pesan' => "Stok untuk {$produk->nama_produk} tidak mencukupi (tersedia: {$produk->stok} {$produk->satuan}).",
                    'ringkasan_teks' => null,
                ];
            }

            $hargaSatuan = (float) $produk->harga;
            $subtotal = $hargaSatuan * $jumlah;
            $totalHarga += $subtotal;

            $validatedItems[] = [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'satuan' => $produk->satuan,
                'harga_satuan' => $hargaSatuan,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($validatedItems)) {
            return [
                'success' => false,
                'draft' => null,
                'pesan' => 'Tidak ada item yang valid untuk diproses.',
                'ringkasan_teks' => null,
            ];
        }

        // Batalkan draft lama milik pengirim ini
        OrderDraft::where('sender_phone', $phone)
            ->where('status', 'menunggu_konfirmasi')
            ->update(['status' => 'dibatalkan']);

        // Buat draft baru yang berlaku selama 2 jam
        $draft = OrderDraft::create([
            'sender_phone' => $phone,
            'sender_name' => $senderName,
            'items' => $validatedItems,
            'total' => $totalHarga,
            'status' => 'menunggu_konfirmasi',
            'expires_at' => now()->addHours(2),
        ]);

        $ringkasanTeks = $this->formatRingkasanPesanan($draft);

        return [
            'success' => true,
            'draft' => $draft,
            'pesan' => 'Draft pesanan berhasil dibuat.',
            'ringkasan_teks' => $ringkasanTeks,
        ];
    }

    /**
     * Format teks ringkasan pesanan WhatsApp sesuai spesifikasi.
     */
    public function formatRingkasanPesanan(OrderDraft $draft): string
    {
        $baris = ["Berikut ringkasan pesanan Anda:\n"];
        $no = 1;

        foreach ($draft->items as $item) {
            $hargaRp = 'Rp '.number_format($item['harga_satuan'], 0, ',', '.');
            $subtotalRp = 'Rp '.number_format($item['subtotal'], 0, ',', '.');
            $baris[] = "{$no}. {$item['nama_produk']} ({$item['jumlah']} {$item['satuan']} × {$hargaRp}) = {$subtotalRp}";
            $no++;
        }

        $totalRp = 'Rp '.number_format((float) $draft->total, 0, ',', '.');
        $baris[] = "\nTotal: *{$totalRp}*";
        $baris[] = 'Metode Pembayaran: *COD (Bayar saat ambil di toko)*';
        $baris[] = "\nApakah pesanan ini sudah benar?";
        $baris[] = 'Ketik *KONFIRMASI* untuk memproses pesanan, atau ketik *BATAL* untuk membatalkan.';

        return implode("\n", $baris);
    }

    /**
     * Batalkan draft pesanan aktif pelanggan.
     */
    public function cancelDraft(string $phone): bool
    {
        $draft = $this->getActiveDraft($phone);
        if ($draft) {
            $draft->update(['status' => 'dibatalkan']);

            return true;
        }

        return false;
    }

    /**
     * Konfirmasi draft dan buat record pesanan resmi ke database dengan database transaction & locking.
     *
     * @return array{success: bool, transaksi: ?Transaksi, pesan: string}
     */
    public function confirmAndCreateOrder(OrderDraft $draft, string $sumber = 'whatsapp'): array
    {
        if (! $draft->isValid()) {
            return [
                'success' => false,
                'transaksi' => null,
                'pesan' => 'Draft pesanan sudah kedaluwarsa atau tidak valid. Silakan buat pesanan baru.',
            ];
        }

        try {
            return DB::transaction(function () use ($draft, $sumber) {
                // 1. Lock dan periksa ulang stok setiap produk
                $items = $draft->items;
                $finalTotal = 0;

                foreach ($items as $item) {
                    $produk = Produk::where('id_produk', $item['id_produk'])->lockForUpdate()->first();

                    if (! $produk || ! $produk->status) {
                        throw new Exception("Produk '{$item['nama_produk']}' sudah tidak aktif atau dihapus.");
                    }

                    if ($produk->stok < $item['jumlah']) {
                        throw new Exception("Maaf, stok untuk '{$produk->nama_produk}' tersisa {$produk->stok} {$produk->satuan}. Transaksi tidak dapat diproses.");
                    }

                    $finalTotal += ($produk->harga * $item['jumlah']);
                }

                // 2. Dapatkan atau buat akun user
                $user = $this->findOrCreateUserByPhone($draft->sender_phone, $draft->sender_name);

                // 3. Buat Transaksi
                $nomorWa = str_starts_with($draft->sender_phone, 'user_') ? ($user->no_telepon ?: '-') : $draft->sender_phone;

                $transaksi = Transaksi::create([
                    'id_user' => $user->id_user,
                    'id_keranjang' => null,
                    'sumber' => $sumber,
                    'nomor_wa' => $nomorWa,
                    'total_harga' => $finalTotal,
                    'status' => 'diproses',
                    'catatan' => 'Pesanan otomatis via '.($sumber === 'website' ? 'AI Chatbot Website' : 'WhatsApp Bot'),
                ]);

                // 4. Buat Detail Transaksi dan kurangi stok produk secara atomik
                foreach ($items as $item) {
                    $produk = Produk::find($item['id_produk']);

                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_produk' => $produk->id_produk,
                        'nama_produk_snapshot' => $produk->nama_produk,
                        'harga_satuan' => $produk->harga,
                        'jumlah' => $item['jumlah'],
                        'subtotal' => $produk->harga * $item['jumlah'],
                    ]);

                    // Kurangi stok
                    $produk->decrement('stok', $item['jumlah']);
                }

                // 5. Buat Pembayaran COD
                Pembayaran::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'metode_pembayaran' => 'COD',
                    'total_bayar' => $finalTotal,
                    'status_pembayaran' => 'Belum Dibayar',
                ]);

                // 6. Tandai draft sebagai selesai
                $draft->update(['status' => 'selesai']);

                return [
                    'success' => true,
                    'transaksi' => $transaksi->fresh(['detail', 'user']),
                    'pesan' => 'Pesanan berhasil dibuat.',
                ];
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'transaksi' => null,
                'pesan' => $e->getMessage(),
            ];
        }
    }

    /**
     * Deteksi niat pemesanan dari kalimat teks pelanggan (Web Chatbot maupun WhatsApp).
     * Mendukung:
     * - "pesan telur ayam negeri segar 1kg"
     * - "beli minyak goreng sania 2 pouch dan beras pandan 1 karung"
     * - "mau beli 2 minyak bimoli"
     *
     * @return array{is_order: bool, items: array<int, array{id_produk: int, jumlah: int}>}
     */
    public function detectOrderIntent(string $text): array
    {
        $lower = strtolower(trim($text));
        $orderKeywords = ['beli', 'pesan', 'order', 'mau ambil', 'ambil', 'mau beli'];

        $isOrder = false;
        foreach ($orderKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $isOrder = true;
                break;
            }
        }

        if (! $isOrder) {
            return ['is_order' => false, 'items' => []];
        }

        // Pisahkan segmen kalimat jika ada pemesanan multi-produk (pemisah: newline, koma, kata "dan", "plus")
        $segments = preg_split('/(\r\n|\n|\r|,|\bdan\b|\bplus\b)/i', $text);
        $items = [];
        $seenProductIds = [];

        foreach ($segments as $segment) {
            $seg = trim($segment);
            if (empty($seg)) {
                continue;
            }

            // Ekstrak angka jumlah (contoh: "1kg", "2 pouch", "3", "1 kg")
            preg_match('/(\d+)\s*(kg|liter|karung|pouch|pcs|botol|bungkus|dus|kaleng)?/i', $seg, $matches);
            $jumlah = isset($matches[1]) ? max(1, (int) $matches[1]) : 1;

            // Bersihkan kata-kata pembantu untuk menyisakan nama produk
            $cleanQuery = preg_replace('/(beli|pesan|order|mau|saya|tolong|sebanyak|\d+\s*(kg|liter|karung|pouch|pcs|botol|bungkus|dus|kaleng)?)/iu', ' ', $seg);
            $cleanQuery = trim(preg_replace('/\s+/', ' ', $cleanQuery));

            if (empty($cleanQuery) || mb_strlen($cleanQuery) < 3) {
                continue;
            }

            $produk = $this->productService->cariProduk($cleanQuery, limit: 1)->first();

            if ($produk && ! in_array($produk->id_produk, $seenProductIds, true)) {
                $seenProductIds[] = $produk->id_produk;
                $items[] = [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $jumlah,
                ];
            }
        }

        // Fallback seluruh kalimat jika segmen tidak mendeteksi
        if (empty($items)) {
            preg_match('/(\d+)\s*(kg|liter|karung|pouch|pcs|botol|bungkus|dus|kaleng)?/i', $text, $matches);
            $jumlah = isset($matches[1]) ? max(1, (int) $matches[1]) : 1;

            $cleanQuery = preg_replace('/(beli|pesan|order|mau|saya|tolong|sebanyak|\d+\s*(kg|liter|karung|pouch|pcs|botol|bungkus|dus|kaleng)?)/iu', ' ', $text);
            $cleanQuery = trim(preg_replace('/\s+/', ' ', $cleanQuery));

            if (! empty($cleanQuery) && mb_strlen($cleanQuery) >= 3) {
                $produk = $this->productService->cariProduk($cleanQuery, limit: 1)->first();
                if ($produk) {
                    $items[] = [
                        'id_produk' => $produk->id_produk,
                        'jumlah' => $jumlah,
                    ];
                }
            }
        }

        return [
            'is_order' => ! empty($items),
            'items' => $items,
        ];
    }
}
