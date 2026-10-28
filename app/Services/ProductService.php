<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Cari produk aktif berdasarkan kata kunci dan/atau kategori.
     */
    public function cariProduk(?string $keyword = null, ?int $idKategori = null, int $limit = 20): Collection
    {
        return Produk::with('kategori')
            ->where('status', true)
            ->when($keyword, function ($query) use ($keyword) {
                $terms = array_filter(explode(' ', trim($keyword)));
                $query->where(function ($sub) use ($terms, $keyword) {
                    $sub->where('nama_produk', 'like', '%'.$keyword.'%')
                        ->orWhere('deskripsi', 'like', '%'.$keyword.'%');

                    foreach ($terms as $term) {
                        $sub->orWhere('nama_produk', 'like', '%'.$term.'%');
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

        if ($katalog->isEmpty()) {
            return 'Tidak ada produk yang cocok dengan pencarian.';
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
