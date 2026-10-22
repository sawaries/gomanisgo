<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;

class TransaksiController extends Controller {
    public function index(Request $r){
        $st=$r->status;
        $transaksi=Transaksi::with('detail.produk','pembayaran')->where('id_user',auth()->id())
            ->when(isset(Transaksi::STATUS[$st]),fn($q)=>$q->where('status',$st))
            ->latest('id_transaksi')->get();
        return view('transaksi',compact('transaksi','st'));
    }
    public function batal($id){
        $t=Transaksi::with('detail')->where('id_user',auth()->id())->findOrFail($id);
        if($t->status!=='diproses') return back()->with('error','Pesanan sudah disiapkan, tidak bisa dibatalkan');
        $t->batalkan();
        return back()->with('success','Pesanan dibatalkan');
    }
}