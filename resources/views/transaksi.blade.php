@extends('layout')
@section('content')
@php
  $steps=['diproses'=>'Diproses','disiapkan'=>'Disiapkan','siap_diambil'=>'Siap Diambil','selesai'=>'Selesai'];
  $pesan=[
    'diproses'=>'Pesanan diterima, menunggu disiapkan oleh toko.',
    'disiapkan'=>'Toko sedang menyiapkan pesananmu.',
    'siap_diambil'=>'Pesanan siap diambil di toko. Sebutkan nomor pesanan dan bayar tunai (COD) di kasir.',
    'selesai'=>'Pesanan selesai. Terima kasih sudah berbelanja!',
    'dibatalkan'=>'Pesanan ini dibatalkan.',
  ];
@endphp
<h1 class="kr-title">Transaksi</h1>
<p class="kr-sub">Riwayat dan status pesanan Anda</p>

<div class="tx-tabs">
  <a href="{{ route('transaksi.index') }}" class="gm-pill {{ !$st ? 'active' : '' }}">Semua</a>
  @foreach(\App\Models\Transaksi::STATUS as $k=>$label)
    <a href="{{ route('transaksi.index',['status'=>$k]) }}" class="gm-pill {{ $st==$k ? 'active' : '' }}">{{ $label }}</a>
  @endforeach
</div>

@forelse($transaksi as $t)
  @php $cur=array_search($t->status,array_keys($steps)); @endphp
  <div class="kr-card" style="margin-bottom:1rem">
    <div class="kr-h">
      <span>Pesanan #{{ $t->id_transaksi }} <span class="gm-sub" style="font-weight:400">· {{ optional($t->tanggal_transaksi)->format('d M Y, H:i') }}</span></span>
      <span class="tx-b {{ $t->status }}">{{ \App\Models\Transaksi::STATUS[$t->status] ?? $t->status }}</span>
    </div>

    @if($t->status!='dibatalkan')
      <div class="tx-steps">
        @foreach($steps as $k=>$label)
          <div class="tx-s {{ $loop->index<=$cur ? 'on' : '' }}"><i>@include('icon',['n'=>'check'])</i>{{ $label }}</div>
        @endforeach
      </div>
    @endif
    <p class="tx-msg {{ $t->status=='siap_diambil' ? 'hot' : '' }}">{{ $pesan[$t->status] ?? '' }}</p>

    @foreach($t->detail as $d)
      @php $p=$d->produk; @endphp
      <div class="kr-row" style="grid-template-columns:56px 1fr auto">
        <div class="kr-img" style="width:56px;height:56px">@if(optional($p)->gambar_url)<img src="{{ $p->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div>
        <div>
          <div class="gm-name" style="min-height:0">{{ optional($p)->nama_produk ?? 'Produk dihapus' }}</div>
          <div class="gm-sub">{{ $d->jumlah }} x Rp {{ number_format($d->harga_satuan,0,',','.') }}</div>
        </div>
        <b class="kr-subt">Rp {{ number_format($d->subtotal,0,',','.') }}</b>
      </div>
    @endforeach

    <div class="kr-b" style="display:flex;flex-wrap:wrap;gap:.6rem;justify-content:space-between;align-items:center">
      <div>
        <div class="gm-sub">COD - Bayar di toko ({{ optional($t->pembayaran)->status_pembayaran ?? '-' }})</div>
        <div style="font-size:.85rem">Total Pesanan <b class="gm-price" style="display:inline;margin-left:.3rem">Rp {{ number_format($t->total_harga,0,',','.') }}</b></div>
      </div>
      @if($t->status=='diproses')
        <form method="POST" action="{{ route('transaksi.batal',$t->id_transaksi) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
          @csrf<button class="ad-btn red">Batalkan Pesanan</button>
        </form>
      @endif
    </div>
  </div>
@empty
  <div class="kr-card kr-b" style="text-align:center;padding:2.5rem 1rem">
    <p class="mb-2">Belum ada transaksi{{ $st ? ' dengan status ini' : '' }}.</p>
    <a href="{{ route('home') }}#produk" class="gm-link">Mulai belanja &gt;</a>
  </div>
@endforelse
@endsection