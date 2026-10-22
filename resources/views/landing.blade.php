@extends('layout')
@section('content')
{{-- Hero --}}
<section class="gm-hero mb-4">
  <h1>Selamat Datang{{ auth()->check() ? ', '.auth()->user()->nama_user : '' }}!</h1>
  <p>Nikmati kemudahan berbelanja kebutuhan pokok dan sembako dengan harga terbaik.</p>
</section>

{{-- Kategori --}}
<section id="kategori" class="mb-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="gm-title">Kategori</h2>
    <a href="{{ route('home') }}#produk" class="gm-link">Lihat Semua &gt;</a>
  </div>
  <div class="gm-cats">
    @foreach($kategori as $k)
      <a href="{{ route('home', ['kategori'=>$k->id_kategori]) }}#produk"
         class="gm-cat {{ request('kategori') == $k->id_kategori ? 'active' : '' }}">
        @if($k->gambar_url)<img src="{{ $k->gambar_url }}" alt="" loading="lazy">@endif{{ $k->nama_kategori }}
      </a>
    @endforeach
  </div>
</section>

{{-- Produk --}}
<section id="produk">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
      <h2 class="gm-title me-1">Produk Unggulan</h2>
      <a href="{{ route('home') }}#produk" class="gm-pill {{ request()->hasAny(['urut','kategori','q']) ? '' : 'active' }}">Semua</a>
      <a href="{{ route('home', ['urut'=>'terbaru']) }}#produk" class="gm-pill {{ request('urut') === 'terbaru' ? 'active' : '' }}">Terbaru</a>
    </div>
    <form method="GET" action="{{ route('home') }}#produk">
      @foreach(request()->except('urut') as $key=>$val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endforeach
      <select name="urut" class="form-select gm-sort" onchange="this.form.submit()">
        <option value="terbaru"  @selected($urut==='terbaru')>Urutkan: Terbaru</option>
        <option value="termurah" @selected($urut==='termurah')>Harga Termurah</option>
        <option value="termahal" @selected($urut==='termahal')>Harga Termahal</option>
      </select>
    </form>
  </div>

  @forelse($produk as $p)
    @if($loop->first)<div class="gm-grid">@endif
    <article class="gm-card">
      <div class="gm-img">
        @if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="{{ $p->nama_produk }}" loading="lazy">
        @else @include('icon',['n'=>'image']) @endif
      </div>
      <h3 class="gm-name">{{ $p->nama_produk }}</h3>
      <div class="gm-sub">{{ optional($p->kategori)->nama_kategori }}{{ $p->satuan ? ' · '.$p->satuan : '' }}</div>
      <div class="gm-price">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
      <div><span class="gm-stok {{ $p->stok <= 10 ? 'low' : '' }}">Stok {{ $p->stok }}</span></div>

      @auth
        <form action="{{ route('keranjang.add', $p->id_produk) }}" method="POST" class="mt-auto">
          @csrf<input type="hidden" name="jumlah" value="1">
          <button class="gm-btn" @disabled($p->stok < 1)>+ Keranjang</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="gm-btn">Login untuk beli</a>
      @endauth
    </article>
    @if($loop->last)</div>@endif
  @empty
    <div class="text-center text-muted py-5">Produk tidak ditemukan.</div>
  @endforelse
</section>
@endsection