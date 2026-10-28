<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Produk;
use App\Models\Kategori;

class ProdukController extends Controller {
    public function index(Request $r){
        $produk=Produk::with('kategori')
            ->when($r->q,fn($q)=>$q->where('nama_produk','like','%'.$r->q.'%'))
            ->when($r->kategori,fn($q)=>$q->where('id_kategori',$r->kategori))
            ->latest('id_produk')->paginate(10)->withQueryString();
        $stat=['total'=>Produk::count(),'aktif'=>Produk::where('status',1)->count(),
               'menipis'=>Produk::where('stok','<=',10)->count(),'kategori'=>Kategori::count()];
        return view('admin.Produk',['produk'=>$produk,'kategori'=>Kategori::all(),'stat'=>$stat]);
    }
    private function data(Request $r,$id=null){
        $d=$r->validate([
            'id_kategori'=>'required|exists:kategori,id_kategori',
            'nama_produk'=>'required|max:150',
            'satuan'=>'required|max:30',
            'harga'=>'required|numeric|min:0',
            'stok'=>'required|integer|min:0',
            'deskripsi'=>'nullable',
            'gambar'=>'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        unset($d['gambar']);
        $d['status']=$r->boolean('status');
        return $d;
    }
    private function hapusFoto($p){ if($p->gambar) Storage::disk('public')->delete($p->gambar); }
    public function store(Request $r){
        $d=$this->data($r);
        if($r->hasFile('gambar')) $d['gambar']=$r->file('gambar')->store('produk','public');
        Produk::create($d);
        return redirect()->route('admin.produk.index')->with('success','Produk ditambahkan');
    }
    public function update(Request $r,$id){
        $p=Produk::findOrFail($id);
        $d=$this->data($r,$id);
        if($r->hasFile('gambar')){ $this->hapusFoto($p); $d['gambar']=$r->file('gambar')->store('produk','public'); }
        $p->update($d);
        return redirect()->route('admin.produk.index')->with('success','Produk diperbarui');
    }
    private function hapus($ids){
        $n=0;
        foreach(Produk::whereIn('id_produk',$ids)->get() as $p){
            try{ $p->delete(); $this->hapusFoto($p); $n++; }catch(\Throwable $e){}
        }
        return back()->with($n==count($ids)?'success':'error',$n.' dari '.count($ids).' produk dihapus (produk yang sudah ada di transaksi tidak bisa dihapus)');
    }
    public function destroy($id){ return $this->hapus([$id]); }
    public function bulk(Request $r){
        if(!$r->ids) return back()->with('error','Pilih produk dulu');
        if($r->aksi=='hapus') return $this->hapus($r->ids);
        Produk::whereIn('id_produk',$r->ids)->update(['status'=>$r->aksi=='aktif']);
        return back()->with('success','Status produk diubah');
    }
}