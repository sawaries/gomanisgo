<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;

class DashboardController extends Controller
{
    public function index()
    {
        $stat = [
            'aktif' => Transaksi::aktif()->count(),
            'menunggu' => Transaksi::where('status', 'diproses')->count(),
            'disiapkan' => Transaksi::where('status', 'disiapkan')->count(),
            'siap' => Transaksi::where('status', 'siap_diambil')->count(),
        ];
        $terbaru = Transaksi::with('user', 'detail')->aktif()->latest('id_transaksi')->limit(6)->get();

        return view('gudang.Dashboard', compact('stat', 'terbaru'));
    }
}