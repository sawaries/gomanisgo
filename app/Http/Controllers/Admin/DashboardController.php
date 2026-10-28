<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Support\Laporan;

class DashboardController extends Controller {
    public function index(){
        $stat=[
            'kategori'=>Kategori::whereHas('produk',fn($q)=>$q->where('status',1))->count(),
            'produk'=>Produk::where('status',1)->count(),
            'penjualan'=>Transaksi::where('status','selesai')->count(),
            'pendapatan'=>Transaksi::where('status','selesai')->sum('total_harga'),
        ];
        return view('admin.Dashboard',['stat'=>$stat,'L'=>Laporan::semua()]);
    }
}