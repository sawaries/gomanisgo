<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\DetailKeranjang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller {
    private function items(Request $r){
        $k=Keranjang::where('id_user',auth()->id())->first();
        if(!$k || !$r->ids) return collect();
        return DetailKeranjang::with('produk.kategori')->where('id_keranjang',$k->id_keranjang)->whereIn('id_produk',$r->ids)->get();
    }
    public function konfirmasi(Request $r){
        $items=$this->items($r);
        if($items->isEmpty()) return redirect()->route('keranjang.index')->with('error','Pilih produk dulu');
        return view('checkout',compact('items'));
    }
    public function process(Request $r){
        $items=$this->items($r);
        if($items->isEmpty()) return redirect()->route('keranjang.index')->with('error','Pilih produk dulu');
        foreach($items as $i){
            if(!$i->produk->status || $i->produk->stok<$i->jumlah)
                return redirect()->route('keranjang.index')->with('error','Stok '.$i->produk->nama_produk.' tidak mencukupi');
        }
        $idUser=auth()->id();
        DB::transaction(function() use($items,$idUser){
            DB::statement('CALL buat_transaksi(?,?)',[$idUser,$items->first()->id_keranjang]);
            $id=Transaksi::where('id_user',$idUser)->max('id_transaksi');
            foreach($items as $i){
                DetailTransaksi::create([
                    'id_transaksi'=>$id,'id_produk'=>$i->id_produk,
                    'harga_satuan'=>$i->produk->harga,'jumlah'=>$i->jumlah,
                    'subtotal'=>$i->jumlah*$i->produk->harga,
                ]);
            }
            Pembayaran::create(['id_transaksi'=>$id,'metode_pembayaran'=>'COD','total_bayar'=>Transaksi::find($id)->total_harga]);
            DetailKeranjang::where('id_keranjang',$items->first()->id_keranjang)->whereIn('id_produk',$items->pluck('id_produk'))->delete();
        });
        return redirect()->route('transaksi.index')->with('success','Pesanan diterima! Tunggu status "Siap Diambil", lalu ambil dan bayar di toko.');
    }
}