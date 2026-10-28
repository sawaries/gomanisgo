<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;

class Laporan
{
    const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    public static function rupiah($n)
    {
        return 'Rp '.number_format($n, 0, ',', '.');
    }

    // 17,5 / 18 (tanpa nol di belakang koma)
    public static function angka($x)
    {
        return rtrim(rtrim(number_format($x, 1, ',', '.'), '0'), ',');
    }

    // Rp18 juta / Rp350 rb / Rp500
    public static function singkat($n)
    {
        if ($n >= 1000000) return 'Rp'.self::angka($n / 1000000).' juta';
        if ($n >= 1000) return 'Rp'.self::angka($n / 1000).' rb';
        return 'Rp'.number_format($n, 0, ',', '.');
    }

    // Semua data untuk grafik & daftar (6 bulan terakhir, hanya pesanan berstatus selesai)
    public static function semua()
    {
        $awal = now()->startOfMonth()->subMonths(5);

        $rows = Transaksi::where('status', 'selesai')->where('tanggal_transaksi', '>=', $awal)
            ->get(['total_harga', 'tanggal_transaksi'])
            ->groupBy(fn ($t) => $t->tanggal_transaksi->format('Y-m'));

        $bulan = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $awal->copy()->addMonths($i);
            $g = $rows->get($m->format('Y-m'));
            $bulan[] = ['nama' => self::BULAN[$m->month], 'total' => $g ? (float) $g->sum('total_harga') : 0];
        }
        $akhir = $bulan[5]['total'];
        $lalu = $bulan[4]['total'];

        $dasar = fn () => DB::table('detail_transaksi as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->join('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
            ->where('t.status', 'selesai')->where('t.tanggal_transaksi', '>=', $awal);

        $top = fn ($id, $nama) => $dasar()->groupBy($id, $nama)->orderByDesc('total')->limit(5)
            ->get([$nama.' as nama', DB::raw('SUM(d.jumlah) as total')]);

        $sama = $awal->year == now()->year;
        return [
            'periode' => ($sama ? self::BULAN[$awal->month] : self::BULAN[$awal->month].' '.$awal->year).' – '.self::BULAN[now()->month].' '.now()->year,
            'bulan' => $bulan,
            'total' => array_sum(array_column($bulan, 'total')),
            'tumbuh' => $lalu > 0 ? ($akhir - $lalu) / $lalu * 100 : null,
            'selisih' => $akhir - $lalu,
            'nama_akhir' => $bulan[5]['nama'],
            'nama_lalu' => $bulan[4]['nama'],
            'pcs' => (int) $dasar()->sum('d.jumlah'),
            'kategori' => $top('k.id_kategori', 'k.nama_kategori'),
            'produk' => $top('p.id_produk', 'p.nama_produk'),
            'aktif' => Transaksi::with('user', 'detail')->aktif()->latest('id_transaksi')->limit(6)->get(),
            'aktifJml' => Transaksi::aktif()->count(),
        ];
    }
}