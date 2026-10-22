<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\DetailKeranjang;
use App\Models\Produk;

class KeranjangController extends Controller {
    private function cart(){ return Keranjang::where('id_user',auth()->id())->first(); }

    public function add($id, Request $r){
        $p=Produk::findOrFail($id);
        if(!$p->status || $p->stok<1) return back()->with('error','Stok produk habis');
        $keranjang=Keranjang::firstOrCreate(['id_user'=>auth()->id()]);
        $item=DetailKeranjang::where('id_keranjang',$keranjang->id_keranjang)->where('id_produk',$id);
        if($item->exists()){ $item->increment('jumlah',$r->jumlah ?? 1); }
        else { DetailKeranjang::create(['id_keranjang'=>$keranjang->id_keranjang,'id_produk'=>$id,'jumlah'=>$r->jumlah ?? 1]); }
        return back()->with('success','Ditambahkan ke keranjang');
    }
    public function index(){
        $keranjang=Keranjang::where('id_user',auth()->id())->with('items.produk.kategori')->first();
        return view('keranjang',compact('keranjang'));
    }
    public function update(Request $r,$id){
        $k=$this->cart();
        $q=DetailKeranjang::where('id_keranjang',$k->id_keranjang)->where('id_produk',$id);
        $row=$q->first();
        if(!$row) return back();
        $n=$row->jumlah+($r->aksi=='tambah' ? 1 : -1);
        if($n<1) $q->delete();
        elseif($n>Produk::where('id_produk',$id)->value('stok')) return back()->with('error','Stok tidak cukup');
        else $q->update(['jumlah'=>$n]);
        return back();
    }
    public function remove($id){
        DetailKeranjang::where('id_keranjang',$this->cart()->id_keranjang)->where('id_produk',$id)->delete();
        return back();
    }
    public function hapusPilih(Request $r){
        if($r->ids) DetailKeranjang::where('id_keranjang',$this->cart()->id_keranjang)->whereIn('id_produk',$r->ids)->delete();
        return back()->with('success','Produk terpilih dihapus');
    }
}