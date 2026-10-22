<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembayaran;

class PesananController extends Controller {
    public function index(Request $r){
        $st=$r->status;
        $urut=$r->urut=='terlama' ? 'asc' : 'desc';
        $num=preg_match('/(\d+)\D*$/',(string)$r->q,$m) ? (int)$m[1] : null;
        $with=['user','detail.produk','pembayaran'];

        $list=Transaksi::with($with)
            ->when(isset(Transaksi::STATUS[$st]),fn($q)=>$q->where('status',$st),fn($q)=>$q->aktif())
            ->when($r->q,fn($q)=>$q->where(fn($w)=>$w
                ->whereHas('user',fn($u)=>$u->where('nama_user','like','%'.$r->q.'%')->orWhere('email','like','%'.$r->q.'%'))
                ->when($num,fn($w2)=>$w2->orWhere('id_transaksi',$num))))
            ->orderBy('id_transaksi',$urut)->get();

        $sel=($r->id ? Transaksi::with($with)->find($r->id) : null) ?? $list->first();

        $aktif=Transaksi::aktif();
        $stat=[
            'aktif'=>(clone $aktif)->count(),
            'menunggu'=>Transaksi::where('status','diproses')->count(),
            'siap'=>Transaksi::where('status','siap_diambil')->count(),
            'disiapkan'=>Transaksi::where('status','disiapkan')->count(),
            'tagihan'=>(clone $aktif)->sum('total_harga'),
            'batal'=>Transaksi::where('status','dibatalkan')->count(),
        ];
        $histori=Transaksi::with('user')->whereIn('status',['selesai','dibatalkan'])
            ->where('tanggal_transaksi','>=',now()->subDays(30))->latest('id_transaksi')->get();

        return view('admin.Pesanan',compact('list','sel','st','stat','histori'));
    }
    public function updateStatus(Request $r,$id){
        $r->validate(['status'=>'required|in:'.implode(',',array_keys(Transaksi::STATUS))]);
        $t=Transaksi::with('detail')->findOrFail($id);
        if(in_array($t->status,['selesai','dibatalkan'])) return back()->with('error','Pesanan '.$t->status.' tidak bisa diubah lagi');
        if($r->status=='dibatalkan') $t->batalkan();
        else {
            $t->update(['status'=>$r->status]);
            if($r->status=='selesai') Pembayaran::where('id_transaksi',$id)->update(['status_pembayaran'=>'Lunas']);
        }
        return back()->with('success','Status pesanan diperbarui');
    }
}