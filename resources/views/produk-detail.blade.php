@extends('layout')
@section('content')
<p class="kr-sub"><a href="{{ route('home') }}#produk" class="gm-link">&lt; Kembali ke produk</a></p>
<div class="kr-card kr-b pd-wrap">
  <div class="pd-img">@if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="{{ $p->nama_produk }}">@else @include('icon',['n'=>'image']) @endif</div>
  <div class="pd-info">
    <span class="gm-sub">{{ optional($p->kategori)->nama_kategori }}{{ $p->satuan ? ' · '.$p->satuan : '' }}</span>
    <h1>{{ $p->nama_produk }}</h1>
    <div class="gm-price">Rp {{ number_format($p->harga,0,',','.') }}</div>
    <span class="gm-stok {{ $p->stok<=10 ? 'low' : '' }}">Stok {{ $p->stok }}</span>
    <p class="pd-desc">{{ $p->deskripsi ?: 'Belum ada deskripsi untuk produk ini.' }}</p>
    @auth
      <form action="{{ route('keranjang.add',$p->id_produk) }}" method="POST">
        @csrf<input type="hidden" name="jumlah" value="1">
        <button class="gm-btn" @disabled($p->stok<1)>+ Keranjang</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="gm-btn">Login untuk beli</a>
    @endauth
  </div>
</div>
@endsection