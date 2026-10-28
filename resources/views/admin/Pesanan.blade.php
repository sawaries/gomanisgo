@extends('admin.layout')
@section('title','Pesanan Masuk')
@section('content')
@php
  $lanjut=['diproses'=>['disiapkan','Siapkan Pesanan'],'disiapkan'=>['siap_diambil','Tandai Siap Diambil'],'siap_diambil'=>['selesai','Selesaikan Pesanan']];
  $tgl=fn($t)=>$t->tanggal_transaksi ? $t->tanggal_transaksi->locale('id')->translatedFormat('d M Y • H:i') : '-';
  $rp=fn($n)=>'Rp '.number_format($n,0,',','.');
  $selesaiJml=$histori->where('status','selesai')->count();
  $batalJml=$histori->where('status','dibatalkan')->count();
@endphp

<h1 class="ad-h">Pesanan Masuk</h1>
<p class="ad-sub">Kelola pesanan aktif, siapkan pengambilan di toko, dan pantau histori transaksi.</p>

<div class="ad-stats">
  <div class="ad-stat"><div><small>Pesanan Aktif</small><b>{{ $stat['aktif'] }}</b><span class="ad-mut">{{ $stat['menunggu'] }} menunggu diproses</span></div></div>
  <div class="ad-stat"><div><small>Siap Diambil</small><b>{{ $stat['siap'] }}</b><span class="ad-mut">{{ $stat['disiapkan'] }} sedang disiapkan</span></div></div>
  <div class="ad-stat"><div><small>Total Tagihan</small><b class="or">{{ $rp($stat['tagihan']) }}</b><span class="ad-mut">{{ $stat['aktif'] }} pesanan via website</span></div></div>
  <div class="ad-stat"><div><small>Dibatalkan</small><b class="red">{{ $stat['batal'] }}</b><span class="ad-mut">total pesanan dibatalkan</span></div></div>
</div>

<form method="GET" class="ad-card ad-bar">
  <input class="ad-in" name="q" value="{{ request('q') }}" placeholder="Cari nomor pesanan atau pelanggan...">
  <select class="ad-in ad-in-sm" name="status" onchange="this.form.submit()">
    <option value="">Semua Status Aktif</option>
    @foreach(\App\Models\Transaksi::STATUS as $k=>$l)<option value="{{ $k }}" @selected($st==$k)>{{ $l }}</option>@endforeach
  </select>
  <select class="ad-in ad-in-sm" name="urut" onchange="this.form.submit()">
    <option value="terbaru">Terbaru</option><option value="terlama" @selected(request('urut')=='terlama')>Terlama</option>
  </select>
</form>

<div class="ps-wrap">
  <div class="ps-col">
    {{-- Daftar pesanan --}}
    <section class="ad-card ch-card">
      <div class="ch-head">
        <div><h2 class="ch-title">{{ isset(\App\Models\Transaksi::STATUS[$st]) ? 'Daftar Pesanan '.\App\Models\Transaksi::STATUS[$st] : 'Daftar Pesanan Aktif' }}</h2>
          <p class="ad-sub">{{ $list->count() }} pesanan · klik baris untuk melihat detail</p></div>
        <span class="ad-badge or">{{ $list->count() }} Pesanan</span>
      </div>
      <div class="ad-tbl-w">
        <table class="ad-tbl">
          <thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Status</th><th>Total</th></tr></thead>
          <tbody>
          @forelse($list as $t)
            <tr class="ps-row {{ $sel && $sel->id_transaksi==$t->id_transaksi ? 'sel' : '' }}"
                onclick="location.href='{{ route('admin.pesanan.index',array_merge(request()->except('id'),['id'=>$t->id_transaksi])) }}'">
              <td><b>#{{ $t->kode }}</b><div class="ad-mut">{{ $tgl($t) }}</div></td>
              <td><b>{{ optional($t->user)->nama_user ?? '-' }}</b><div class="ad-mut">{{ optional($t->user)->email }}</div></td>
              <td><span class="tx-b {{ $t->status }}">{{ \App\Models\Transaksi::STATUS[$t->status] }}</span><div class="ad-mut">{{ $t->detail->count() }} item</div></td>
              <td><b>{{ $rp($t->total_harga) }}</b></td>
            </tr>
          @empty
            <tr><td colspan="4" class="ad-empty">Tidak ada pesanan.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- Histori 30 hari --}}
    <section class="ad-card ch-card">
      <div class="ch-head">
        <div><h2 class="ch-title">Histori Pesanan</h2><p class="ad-sub">Pesanan selesai dan dibatalkan dalam 30 hari terakhir</p></div>
        <span class="ad-badge or">30 Hari</span>
      </div>
      <div class="ps-trio">
        <div class="ps-mini"><small>Selesai</small><b>{{ $selesaiJml }}</b></div>
        <div class="ps-mini hot"><small>Dibatalkan</small><b>{{ $batalJml }}</b></div>
        <div class="ps-mini"><small>Total</small><b>{{ $histori->count() }}</b></div>
      </div>
      @forelse($histori->take(6) as $h)
        <div class="ps-hist">
          <i class="ps-ico {{ $h->status }}">@include('icon',['n'=>$h->status=='selesai' ? 'check' : 'x'])</i>
          <div><b>Pesanan #{{ $h->kode }} {{ $h->status }}</b><div class="ad-mut">{{ $tgl($h) }} • {{ optional($h->user)->nama_user ?? '-' }}</div></div>
          <span class="tx-b {{ $h->status }}">{{ \App\Models\Transaksi::STATUS[$h->status] }}</span>
        </div>
      @empty
        <p class="ad-mut">Belum ada riwayat dalam 30 hari terakhir.</p>
      @endforelse
    </section>
  </div>

  {{-- Detail pesanan --}}
  <aside class="ps-side ad-card ch-card">
    @if($sel)
      <div class="ch-head"><h2 class="ch-title">Detail Pesanan</h2><span class="tx-b {{ $sel->status }}">{{ \App\Models\Transaksi::STATUS[$sel->status] }}</span></div>
      <div class="ps-id">
        <i class="ps-ava">{{ strtoupper(substr(optional($sel->user)->nama_user ?? 'P',0,2)) }}</i>
        <div><b>#{{ $sel->kode }}</b><div class="ad-mut">{{ $tgl($sel) }}</div></div>
      </div>
      <dl class="ps-dl">
        <div><dt>Pelanggan</dt><dd>{{ optional($sel->user)->nama_user ?? '-' }}</dd></div>
        <div><dt>Metode Pembayaran</dt><dd>COD (Bayar di Toko)</dd></div>
        <div><dt>Status Pembayaran</dt><dd>{{ optional($sel->pembayaran)->status_pembayaran ?? '-' }}</dd></div>
        <div><dt>Total Tagihan</dt><dd class="or">{{ $rp($sel->total_harga) }}</dd></div>
      </dl>

      <h3 class="ps-h">Item Pesanan</h3>
      @foreach($sel->detail as $d)
        <div class="ps-item">
          <div class="ps-img">@if(optional($d->produk)->gambar_url)<img src="{{ $d->produk->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div>
          <div><b>{{ optional($d->produk)->nama_produk ?? 'Produk dihapus' }}</b><div class="ad-mut">Qty {{ $d->jumlah }} • {{ $rp($d->harga_satuan) }} / pcs</div></div>
          <b>{{ $rp($d->subtotal) }}</b>
        </div>
      @endforeach

      <h3 class="ps-h">Ringkasan Pembayaran</h3>
      <dl class="ps-dl">
        <div><dt>Subtotal</dt><dd>{{ $rp($sel->detail->sum('subtotal')) }}</dd></div>
        <div class="tot"><dt>Total Tagihan</dt><dd class="or">{{ $rp($sel->total_harga) }}</dd></div>
      </dl>
      <p class="kr-info">Pesanan diambil langsung di toko. Pembayaran tunai (COD) dilakukan di kasir saat pengambilan.</p>

      @if(!in_array($sel->status,['selesai','dibatalkan']))
        <div class="ps-btns">
          @if(isset($lanjut[$sel->status]))
            <form method="POST" action="{{ route('admin.pesanan.status',$sel->id_transaksi) }}">@csrf @method('PUT')
              <input type="hidden" name="status" value="{{ $lanjut[$sel->status][0] }}">
              <button class="ad-btn or ad-full">@include('icon',['n'=>'box']) {{ $lanjut[$sel->status][1] }}</button>
            </form>
          @endif
          <form method="POST" action="{{ route('admin.pesanan.status',$sel->id_transaksi) }}">@csrf @method('PUT')
            <select name="status" class="ad-in" onchange="if(this.value)this.form.submit()">
              <option value="">Ubah Status</option>
              @foreach(\App\Models\Transaksi::STATUS as $k=>$l)@if($k!=$sel->status && $k!='dibatalkan')<option value="{{ $k }}">{{ $l }}</option>@endif @endforeach
            </select>
          </form>
          <form method="POST" action="{{ route('admin.pesanan.status',$sel->id_transaksi) }}" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">@csrf @method('PUT')
            <input type="hidden" name="status" value="dibatalkan">
            <button class="ad-btn red ad-full">@include('icon',['n'=>'trash']) Batalkan</button>
          </form>
        </div>
      @endif
    @else
      <div class="ch-head"><h2 class="ch-title">Detail Pesanan</h2></div>
      <p class="ad-mut">Pilih pesanan di daftar untuk melihat detailnya.</p>
    @endif
  </aside>
</div>
@endsection