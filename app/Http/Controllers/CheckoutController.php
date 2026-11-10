<?php

namespace App\Http\Controllers;

use App\Models\DetailKeranjang;
use App\Models\DetailTransaksi;
use App\Models\Keranjang;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function items(Request $r)
    {
        $k = Keranjang::where('id_user', auth()->id())->first();
        if (! $k || ! $r->ids) {
            return collect();
        }

        return DetailKeranjang::with('produk.kategori')->where('id_keranjang', $k->id_keranjang)->whereIn('id_produk', $r->ids)->get();
    }

    public function konfirmasi(Request $r)
    {
        $items = $this->items($r);
        if ($items->isEmpty()) {
            return redirect()->route('keranjang.index')->with('error', 'Pilih produk dulu');
        }

        return view('checkout', compact('items'));
    }

    public function process(Request $r)
    {
        $items = $this->items($r);
        if ($items->isEmpty()) {
            return redirect()->route('keranjang.index')->with('error', 'Pilih produk dulu');
        }
        foreach ($items as $i) {
            if (! $i->produk->status || $i->produk->stok < $i->jumlah) {
                return redirect()->route('keranjang.index')->with('error', 'Stok '.$i->produk->nama_produk.' tidak mencukupi');
            }
        }
        $idUser = auth()->id();
        $totalHarga = $items->sum(fn ($i) => $i->jumlah * $i->produk->harga);

        DB::transaction(function () use ($items, $idUser, $totalHarga) {
            $transaksi = Transaksi::create([
                'id_user' => $idUser,
                'id_keranjang' => $items->first()->id_keranjang,
                'sumber' => 'website',
                'nomor_wa' => auth()->user()->no_telepon ?? null,
                'total_harga' => $totalHarga,
                'status' => 'diproses',
            ]);

            $id = $transaksi->id_transaksi;

            foreach ($items as $i) {
                DetailTransaksi::create([
                    'id_transaksi' => $id,
                    'id_produk' => $i->id_produk,
                    'nama_produk_snapshot' => $i->produk->nama_produk,
                    'harga_satuan' => $i->produk->harga,
                    'jumlah' => $i->jumlah,
                    'subtotal' => $i->jumlah * $i->produk->harga,
                ]);
            }

            Pembayaran::create([
                'id_transaksi' => $id,
                'metode_pembayaran' => 'COD',
                'total_bayar' => $totalHarga,
                'status_pembayaran' => 'Belum Dibayar',
            ]);

            DetailKeranjang::where('id_keranjang', $items->first()->id_keranjang)
                ->whereIn('id_produk', $items->pluck('id_produk'))
                ->delete();
        });

        return redirect()->route('transaksi.index')->with('pesanan_berhasil','Pantau statusnya di halaman ini. Saat berstatus "Siap Diambil", ambil dan bayar di toko.');
    }
}
