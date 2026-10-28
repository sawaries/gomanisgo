@extends('layout')
@section('content')
@php
  $toko=['nama'=>'Toko Grosir Sumber Manis','alamat'=>'Jl. Mawar No. 12, RT 03/RW 05, Kota Bekasi, Jawa Barat','jam'=>'Senin - Sabtu, 08.00 - 20.00 WIB','telp'=>'+62 812 3456 7890'];
  $total=$items->sum(fn($i)=>$i->produk->harga*$i->jumlah);
@endphp
<h1 class="kr-title">Konfirmasi Pesanan</h1>
<p class="kr-sub">Periksa kembali pesanan Anda sebelum dikonfirmasi</p>

<div class="kr-wrap">
  <div class="kr-col">
    <div class="kr-card">
      <div class="kr-h">Toko Pengambilan</div>
      <div class="kr-b">
        <b>{{ $toko['nama'] }}</b>
        <p class="gm-sub" style="margin:.2rem 0">{{ $toko['telp'] }}</p>
        <p style="font-size:.78rem;margin:.4rem 0 1rem">{{ $toko['alamat'] }}</p>
        <div class="kr-step"><span class="kr-n">1</span><div><b>Ambil di Toko</b><small>Pesanan diambil langsung di toko, tidak ada pengiriman.</small></div></div>
        <div class="kr-step"><span class="kr-n">2</span><div><b>Pantau Status</b><small>Diproses, Disiapkan, lalu Siap Diambil. Cek di halaman Transaksi.</small></div></div>
        <div class="kr-step"><span class="kr-n">3</span><div><b>Bayar saat Ambil</b><small>Pembayaran tunai (COD) di kasir. Jam operasional: {{ $toko['jam'] }}.</small></div></div>
      </div>
    </div>

    <div class="kr-card">
      <div class="kr-h">Ringkasan Pesanan <span class="ad-badge off">{{ $items->count() }} item</span></div>
      @foreach($items as $it)
        @php $p=$it->produk; @endphp
        <div class="kr-row" style="grid-template-columns:64px 1fr auto">
          <div class="kr-img">@if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div>
          <div>
            <div class="gm-name" style="min-height:0">{{ $p->nama_produk }}</div>
            <div class="gm-sub">{{ $it->jumlah }} x Rp {{ number_format($p->harga,0,',','.') }}</div>
          </div>
          <b class="kr-subt">Rp {{ number_format($p->harga*$it->jumlah,0,',','.') }}</b>
        </div>
      @endforeach
    </div>
  </div>

  <aside class="kr-side">
    <div class="kr-card">
      <div class="kr-h">Ringkasan Pembayaran</div>
      <div class="kr-b kr-sum">
        <div><span>Subtotal ({{ $items->count() }} item)</span><b>Rp {{ number_format($total,0,',','.') }}</b></div>
        <div class="tot"><span>Total Tagihan</span><b class="gm-price" style="margin:0">Rp {{ number_format($total,0,',','.') }}</b></div>
        <div class="kr-pay">BAYAR DI TOKO (COD)<small style="display:block;font-weight:400">Bayar tunai saat mengambil pesanan.</small></div>

        <form method="POST" action="{{ route('checkout.process') }}" style="margin-top:.8rem">
          @csrf
          @foreach($items as $it)<input type="hidden" name="ids[]" value="{{ $it->id_produk }}">@endforeach
          <button class="gm-btn or">@include('icon',['n'=>'check']) Konfirmasi Pesanan</button>
        </form>
        <a href="{{ route('keranjang.index') }}" class="gm-btn out" style="margin-top:.5rem">Kembali ke Keranjang</a>
      </div>
    </div>
  </aside>
</div>
@endsection