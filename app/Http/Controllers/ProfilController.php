<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Produk;
use App\Models\Transaksi;

class ProfilController extends Controller {
    public function show(){
        $u=auth()->user();
        $hitung=Transaksi::where('id_user',$u->id_user)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n','status');
        $terakhir=Transaksi::with('detail')->where('id_user',$u->id_user)->latest('id_transaksi')->limit(3)->get();
        $belanja=Transaksi::where('id_user',$u->id_user)->where('status','selesai')->sum('total_harga');
        $ids=DB::table('produk_dilihat')->where('id_user',$u->id_user)->orderByDesc('dilihat_pada')->limit(6)->pluck('id_produk')->all();
        $dilihat=Produk::whereIn('id_produk',$ids)->get()->sortBy(fn($p)=>array_search($p->id_produk,$ids))->values();
        return view('profil',compact('u','hitung','terakhir','belanja','dilihat'));
    }
    public function update(Request $r){
        $u=auth()->user();
        $r->validate([
            'nama_user'=>'required|max:100',
            'no_telepon'=>'required|max:20',
            'email'=>'required|email|max:100|unique:users,email,'.$u->id_user.',id_user',
            'password_baru'=>'nullable|min:6',
            'password_lama'=>'required_with:password_baru',
        ]);
        if($r->filled('password_baru')){
            if(!Hash::check($r->password_lama,$u->password))
                return back()->withErrors(['password_lama'=>'Password saat ini salah'])->withInput();
            $u->password=Hash::make($r->password_baru);
        }
        $u->fill($r->only('nama_user','no_telepon','email'))->save();
        return redirect()->route('profil')->with('success','Profil berhasil diperbarui');
    }
}