<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Produk;

class DetailProdukController extends Controller {
    public function show($id){
        $p=Produk::with('kategori')->where('status',1)->findOrFail($id);
        if(auth()->check()){
            DB::table('produk_dilihat')->updateOrInsert(
                ['id_user'=>auth()->id(),'id_produk'=>$p->id_produk],
                ['dilihat_pada'=>now()]
            );
        }
        return view('produk-detail',compact('p'));
    }
}