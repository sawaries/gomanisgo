<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Produk;

class HomeController extends Controller {
    public function index(Request $r){
        $kategori=Kategori::all();
        $urut=$r->urut ?? 'terbaru';
        $produk=Produk::with('kategori')->where('status',1)
            ->when($r->kategori,fn($q)=>$q->where('id_kategori',$r->kategori))
            ->when($r->q,fn($q)=>$q->where('nama_produk','like','%'.$r->q.'%'))
            ->when($urut=='termurah',fn($q)=>$q->orderBy('harga'))
            ->when($urut=='termahal',fn($q)=>$q->orderByDesc('harga'))
            ->when($urut=='terbaru',fn($q)=>$q->orderByDesc('id_produk'))
            ->get();
        return view('landing',compact('kategori','produk','urut'));
    }
}