<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService,
        protected ProductService $productService,
        protected OrderService $orderService
    ) {}

    /**
     * Handle incoming chat message from authenticated user on the website.
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array',
            'history.*.role' => 'nullable|in:user,model',
            'history.*.text' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $message = trim($validated['message']);
        $history = $validated['history'] ?? [];
        $upperText = strtoupper($message);

        // Gunakan nomor telepon user atau ID unik jika nomor telepon belum ada
        $identifier = $user->no_telepon ?: ('user_'.$user->id_user);

        // 1. Alur Pembatalan: BATAL
        if ($upperText === 'BATAL') {
            $canceled = $this->orderService->cancelDraft($identifier);
            $reply = $canceled
                ? "Draft pesanan Anda telah dibatalkan. 🙏\nSilakan sebutkan produk sembako lain jika Anda ingin mencari barang lainnya."
                : 'Tidak ada pesanan aktif yang sedang menunggu konfirmasi.';

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'user' => $user->nama_user,
            ]);
        }

        // 2. Alur Konfirmasi: KONFIRMASI / YA / SETUJU
        if ($upperText === 'KONFIRMASI' || $upperText === 'YA' || $upperText === 'SETUJU') {
            $activeDraft = $this->orderService->getActiveDraft($identifier);

            if ($activeDraft) {
                $result = $this->orderService->confirmAndCreateOrder($activeDraft, sumber: 'website');

                if ($result['success']) {
                    $tx = $result['transaksi'];
                    $totalRp = 'Rp '.number_format((float) $tx->total_harga, 0, ',', '.');
                    $reply = "Pesanan Anda berhasil dibuat! 🎉\n\n"
                        ."Nomor Pesanan: **#{$tx->kode}**\n"
                        ."Total: **{$totalRp}**\n"
                        ."Status: **Diproses (Menunggu disiapkan di toko)**\n"
                        ."Metode Pembayaran: **COD (Bayar saat ambil di toko)**\n\n"
                        .'Terima kasih telah berbelanja di **TokoSembwko**! Pesanan Anda telah otomatis tercatat dalam sistem dan dapat dipantau di menu Transaksi.';
                } else {
                    $reply = "Mohon maaf, konfirmasi pesanan tidak dapat diselesaikan:\n{$result['pesan']}\n\nSilakan ajukan pesanan baru.";
                }

                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                    'user' => $user->nama_user,
                ]);
            }
        }

        // 3. Deteksi Niat Pemesanan (Pesan / Beli produk tertentu)
        $orderIntent = $this->orderService->detectOrderIntent($message);

        if ($orderIntent['is_order'] && ! empty($orderIntent['items'])) {
            $draftResult = $this->orderService->createDraft($identifier, $orderIntent['items'], $user->nama_user);

            if ($draftResult['success']) {
                return response()->json([
                    'success' => true,
                    'reply' => $draftResult['ringkasan_teks'],
                    'user' => $user->nama_user,
                ]);
            }

            return response()->json([
                'success' => true,
                'reply' => "Maaf, pesanan belum dapat diproses:\n{$draftResult['pesan']}",
                'user' => $user->nama_user,
            ]);
        }

        // 4. Tanya jawab produk & rekomendasi via Gemini AI / Fallback Sembako
        $contextTambahan = 'Pelanggan saat ini: '.($user ? $user->nama_user : 'Tamu');

        try {
            $result = $this->geminiService->generateReply($message, $history, $contextTambahan);

            return response()->json([
                'success' => $result['success'],
                'reply' => $result['reply'],
                'user' => $user ? $user->nama_user : null,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'reply' => 'Maaf, terjadi kesalahan pada server saat memproses pesan Anda.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Endpoint cepat untuk mencari produk yang dapat diakses oleh widget jika diperlukan.
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $produk = $this->productService->cariProduk($q, limit: 10);

        return response()->json([
            'data' => $produk->map(fn ($p) => [
                'id' => $p->id_produk,
                'nama' => $p->nama_produk,
                'harga' => $p->harga,
                'satuan' => $p->satuan,
                'stok' => $p->stok,
                'gambar_url' => $p->gambar_url,
            ]),
        ]);
    }
}
