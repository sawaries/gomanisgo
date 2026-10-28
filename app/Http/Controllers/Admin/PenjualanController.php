<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Support\Laporan;
use App\Support\Xlsx;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    private function bulan(Request $r)
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $r->bulan) ? $r->bulan : now()->format('Y-m');
    }

    private function riwayat($bulan)
    {
        [$y,$m] = explode('-', $bulan);

        return Transaksi::with('user', 'detail.produk')->where('status', 'selesai')
            ->whereYear('tanggal_transaksi', $y)->whereMonth('tanggal_transaksi', $m)
            ->orderBy('tanggal_transaksi')->get();
    }

    public function index(Request $r)
    {
        $bulan = $this->bulan($r);

        return view('admin.Penjualan', [
            'L' => Laporan::semua(),
            'bulan' => $bulan,
            'riwayat' => $this->riwayat($bulan)->reverse()->values(),
            'kategoriAktif' => Kategori::whereHas('produk', fn ($q) => $q->where('status', 1))->count(),
        ]);
    }

    // Rekap bulanan: unduh Excel (.xlsx); kalau ekstensi zip PHP tidak aktif, unduh CSV
    public function export(Request $r)
    {
        $bulan = $this->bulan($r);
        $rows = [['No', 'Kode Pesanan', 'Tanggal', 'Pelanggan', 'Produk', 'Jumlah Item', 'Total (Rp)']];
        $total = 0;
        $pcs = 0;
        foreach ($this->riwayat($bulan) as $i => $t) {
            $rows[] = [$i + 1, $t->kode, $t->tanggal_transaksi->format('d/m/Y H:i'), optional($t->user)->nama_user ?? '-',
                $t->detail->map(fn ($d) => optional($d->produk)->nama_produk.' x'.$d->jumlah)->join(', '),
                (int) $t->detail->sum('jumlah'), (float) $t->total_harga];
            $total += $t->total_harga;
            $pcs += $t->detail->sum('jumlah');
        }
        $rows[] = ['', '', '', '', 'TOTAL', (int) $pcs, (float) $total];

        $nama = 'penjualan-'.$bulan;
        $path = Xlsx::make($rows, 'Penjualan '.$bulan, [6, 22, 18, 24, 60, 13, 16], true);
        if ($path) {
            return response()->download($path, $nama.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
        }

        return response()->streamDownload(function () use ($rows) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($f, $row, ';');
            }
            fclose($f);
        }, $nama.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
