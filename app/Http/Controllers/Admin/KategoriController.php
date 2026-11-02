<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller {
    public function index(){ return view('admin.Kategori',['kategori'=>Kategori::withCount('produk')->get()]); }
    private function data(Request $r){
        $d=$r->validate(['nama_kategori'=>'required|max:100','deskripsi'=>'nullable']);
        $d['status']=$r->boolean('status');
        return $d;
    }
    public function store(Request $r){
        Kategori::create($this->data($r));
        return redirect()->route('admin.kategori.index')->with('success','Kategori ditambahkan');
    }
    public function update(Request $r,$id){
        Kategori::findOrFail($id)->update($this->data($r));
        return redirect()->route('admin.kategori.index')->with('success','Kategori diperbarui');
    }
    // tombol on/off cepat di tabel
    public function toggle($id){
        $k=Kategori::findOrFail($id);
        $k->update(['status'=>!$k->status]);
        return back()->with('success','Kategori '.($k->status ? 'diaktifkan' : 'dinonaktifkan'));
    }
    public function destroy($id){
        $k=Kategori::findOrFail($id);
        if($k->produk()->exists()) return back()->with('error','Kategori masih punya produk');
        $k->delete();
        return back()->with('success','Kategori dihapus');
    }
}