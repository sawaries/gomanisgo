<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Bersihkan teks dari kata-kata umum percakapan untuk mengambil kata kunci produk sembako murni.
     *
     * @return array<int, string>
     */
    public function ekstrakKataKunci(string $text): array
    {
        $clean = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $words = array_filter(explode(' ', $clean));

        $stopwords = [
            'pesan', 'beli', 'order', 'mau', 'ambil', 'minta', 'tolong', 'bisa', 'saya', 'anda',
            'kami', 'dong', 'kak', 'min', 'gan', 'sis', 'ada', 'apakah', 'merk', 'merek', 'yang',
            'tersedia', 'harga', 'berapa', 'apa', 'saja', 'di', 'ke', 'dari', 'ini', 'itu',
            'dan', 'atau', 'buat', 'untuk', 'stok', 'promo', 'diskon', 'gak', 'nggak', 'ga',
            'halo', 'hai', 'hello', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'assalamualaikum',
            'kg', 'liter', 'karung', 'pcs', 'pouch', 'bungkus', 'botol', 'dus', 'kaleng',
            '1kg', '2kg', '5kg', '10kg', '1l', '2l', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
        ];

        return array_values(array_filter($words, function ($w) use ($stopwords) {
            return mb_strlen($w) >= 2 && ! in_array($w, $stopwords, true);
        }));
    }

    /**
     * Cari produk aktif berdasarkan kata kunci dan/atau kategori.
     * Menggunakan strategi AND terlebih dahulu untuk mencegah hasil yang tidak relevan.
     */
    public function cariProduk(?string $keyword = null, ?int $idKategori = null, int $limit = 20): Collection
    {
        $keywords = $keyword ? $this->ekstrakKataKunci($keyword) : [];

        // 1. Prioritaskan pencarian presisi: cocok dengan SEMUA kata kunci produk (AND)
        if (! empty($keywords)) {
            $queryAnd = Produk::with('kategori')
                ->where('status', true)
                ->when($idKategori, fn ($q) => $q->where('id_kategori', $idKategori))
                ->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $term) {
                        $sub->where(function ($w) use ($term) {
                            $w->where('nama_produk', 'like', '%'.$term.'%')
                                ->orWhere('deskripsi', 'like', '%'.$term.'%');
                        });
                    }
                })
                ->orderBy('nama_produk')
                ->limit($limit);

            $hasilAnd = $queryAnd->get();
            if ($hasilAnd->isNotEmpty()) {
                return $hasilAnd;
            }
        }

        // 2. Jika tidak ada yang cocok dengan semua kata kunci, gunakan pencarian fleksibel
        return Produk::with('kategori')
            ->where('status', true)
            ->when(! empty($keywords) || $keyword, function ($query) use ($keywords, $keyword) {
                $query->where(function ($sub) use ($keywords, $keyword) {
                    if (! empty($keyword)) {
                        $sub->where('nama_produk', 'like', '%'.trim($keyword).'%')
                            ->orWhere('deskripsi', 'like', '%'.trim($keyword).'%');
                    }

                    foreach ($keywords as $term) {
                        $sub->orWhere('nama_produk', 'like', '%'.$term.'%')
                            ->orWhere('deskripsi', 'like', '%'.$term.'%');
                    }
                });
            })
            ->when($idKategori, fn ($q) => $q->where('id_kategori', $idKategori))
            ->orderBy('nama_produk')
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil seluruh produk aktif dengan stok untuk konteks AI.
     */
    public function getKatalogUntukAI(): array
    {
        return Produk::with('kategori')
            ->where('status', true)
            ->get(['id_produk', 'id_kategori', 'nama_produk', 'satuan', 'harga', 'stok', 'deskripsi'])
            ->map(function ($p) {
                return [
                    'id' => $p->id_produk,
                    'nama' => $p->nama_produk,
                    'kategori' => $p->kategori ? $p->kategori->nama_kategori : 'Umum',
                    'satuan' => $p->satuan ?? 'pcs',
                    'harga' => (float) $p->harga,
                    'stok' => (int) $p->stok,
                    'tersedia' => $p->stok > 0,
                    'deskripsi' => $p->deskripsi ?? '',
                ];
            })
            ->toArray();
    }

    /**
     * Format katalog produk menjadi teks terstruktur yang ringkas untuk context AI.
     */
    public function formatKatalogTeks(?string $filterQuery = null): string
    {
        $katalog = $filterQuery ? $this->cariProduk($filterQuery, limit: 15) : $this->cariProduk(limit: 50);

        if ($katalog->isEmpty() && $filterQuery) {
            $katalog = $this->cariProduk(limit: 30);
        }

        if ($katalog->isEmpty()) {
            return 'Belum ada produk aktif yang terdaftar di database toko.';
        }

        $baris = [];
        foreach ($katalog as $p) {
            $statusStok = $p->stok > 0 ? "Stok: {$p->stok} {$p->satuan}" : 'STOK HABIS';
            $kategori = $p->kategori ? "[{$p->kategori->nama_kategori}] " : '';
            $hargaRp = 'Rp '.number_format((float) $p->harga, 0, ',', '.');
            $baris[] = "- ID: {$p->id_produk} | {$kategori}{$p->nama_produk} | Harga: {$hargaRp} / {$p->satuan} | {$statusStok}";
        }

        return implode("\n", $baris);
    }

    /**
     * Periksa ketersediaan dan validasi harga satu atau beberapa produk.
     *
     * @return array{tersedia: bool, produk: Produk|null, pesan: string}
     */
    public function cekKetersediaan(int $idProduk, int $jumlah = 1): array
    {
        $produk = Produk::find($idProduk);

        if (! $produk) {
            return [
                'tersedia' => false,
                'produk' => null,
                'pesan' => 'Produk tidak ditemukan.',
            ];
        }

        if (! $produk->status) {
            return [
                'tersedia' => false,
                'produk' => $produk,
                'pesan' => "Produk {$produk->nama_produk} sedang tidak aktif/tersedia.",
            ];
        }

        if ($produk->stok < $jumlah) {
            return [
                'tersedia' => false,
                'produk' => $produk,
                'pesan' => "Stok {$produk->nama_produk} tidak mencukupi (tersedia: {$produk->stok} {$produk->satuan}).",
            ];
        }

        return [
            'tersedia' => true,
            'produk' => $produk,
            'pesan' => 'Produk tersedia.',
        ];
    }
}
