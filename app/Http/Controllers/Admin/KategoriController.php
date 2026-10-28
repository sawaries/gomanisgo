<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KategoriController extends Controller
{
    public function index()
    {
        return view('admin.Kategori', ['kategori' => Kategori::withCount('produk')->get()]);
    }

    private function data(Request $r)
    {
        $d = $r->validate([
            'nama_kategori' => 'required|max:100',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        unset($d['gambar']);

        return $d;
    }

    public function store(Request $r)
    {
        $d = $this->data($r);
        if ($r->hasFile('gambar')) {
            $d['gambar'] = $r->file('gambar')->store('kategori', 'public');
        }
        Kategori::create($d);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori ditambahkan');
    }

    public function update(Request $r, $id)
    {
        $k = Kategori::findOrFail($id);
        $d = $this->data($r);
        if ($r->hasFile('gambar')) {
            if ($k->gambar) {
                Storage::disk('public')->delete($k->gambar);
            }
            $d['gambar'] = $r->file('gambar')->store('kategori', 'public');
        }
        $k->update($d);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori diperbarui');
    }

    public function destroy($id)
    {
        $k = Kategori::findOrFail($id);
        if ($k->produk()->exists()) {
            return back()->with('error', 'Kategori masih punya produk');
        }
        if ($k->gambar) {
            Storage::disk('public')->delete($k->gambar);
        }
        $k->delete();

        return back()->with('success', 'Kategori dihapus');
    }
}
