@extends('layout')
@section('content')
@php $items=optional($keranjang)->items ?? collect(); $n=$items->count(); @endphp
<h1 class="kr-title">Keranjang Belanja</h1>
<p class="kr-sub">{{ $n }} produk siap dipesan untuk diambil di toko</p>

@if(!$n)
  <div class="kr-card kr-b" style="text-align:center;padding:2.5rem 1rem">
    <p class="mb-2">Keranjang masih kosong.</p>
    <a href="{{ route('home') }}#produk" class="gm-link">Mulai belanja &gt;</a>
  </div>
@else
<form id="pilih" method="POST" action="{{ route('checkout.konfirmasi') }}">@csrf</form>

<div class="kr-wrap">
  <div class="kr-col">
    <div class="kr-card">
      <div class="kr-h">
        <label class="kr-chk"><input type="checkbox" id="all" checked> Pilih Semua (<span id="cnt">{{ $n }}</span> Produk)</label>
        <button form="pilih" formaction="{{ route('keranjang.hapus-pilih') }}" class="ad-btn red" onclick="return confirm('Hapus produk terpilih?')">@include('icon',['n'=>'trash']) Hapus Dipilih</button>
      </div>
      @foreach($items as $it)
        @php $p=$it->produk; $sub=$p->harga*$it->jumlah; @endphp
        <div class="kr-row">
          <input type="checkbox" class="cb" name="ids[]" value="{{ $it->id_produk }}" form="pilih" data-sub="{{ $sub }}" checked>
          <div class="kr-img">@if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div>
          <div>
            <div class="gm-name" style="min-height:0">{{ $p->nama_produk }}</div>
            <div class="gm-sub">{{ optional($p->kategori)->nama_kategori }}{{ $p->satuan ? ' · '.$p->satuan : '' }}</div>
            <div class="gm-price" style="margin:.2rem 0 0">Rp {{ number_format($p->harga,0,',','.') }}</div>
          </div>
          <div class="kr-ctl">
            <form method="POST" action="{{ route('keranjang.update',$it->id_produk) }}" class="kr-qty">
              @csrf @method('PUT')
              <button name="aksi" value="kurang">@include('icon',['n'=>'minus'])</button>
              <span>{{ $it->jumlah }}</span>
              <button name="aksi" value="tambah" class="plus">@include('icon',['n'=>'plus'])</button>
            </form>
            <b class="kr-subt">Rp {{ number_format($sub,0,',','.') }}</b>
            <form method="POST" action="{{ route('keranjang.remove',$it->id_produk) }}">
              @csrf @method('DELETE')<button class="ad-ic red" title="Hapus">@include('icon',['n'=>'trash'])</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="kr-info">
      <b>@include('icon',['n'=>'bag']) Ambil di Toko</b>
      <p>Pesanan diambil langsung di toko dan dibayar tunai (COD) saat pengambilan. Tidak ada pengiriman.</p>
    </div>
  </div>

  <aside class="kr-side">
    <div class="kr-card">
      <div class="kr-h">Ringkasan Belanja</div>
      <div class="kr-b kr-sum">
        <div><span>Total Produk (<span id="itm">0</span> item)</span><b id="sub">Rp 0</b></div>
        <div class="tot"><span>Total Pembayaran</span><b id="tot" class="gm-price" style="margin:0">Rp 0</b></div>
        <div class="kr-pay">BAYAR DI TOKO (COD)</div>
        <button form="pilih" id="lanjut" class="gm-btn or" style="margin-top:.8rem">Lanjut ke Transaksi</button>
        <a href="{{ route('home') }}#produk" class="gm-btn out" style="margin-top:.5rem">Lanjut Belanja</a>
      </div>
    </div>
    <div class="kr-card">
      <div class="kr-h">Cara Pembayaran</div>
      <div class="kr-b">
        <div class="kr-step"><span class="kr-n">1</span><div><b>Pesan lewat Website</b><small>Pilih produk lalu konfirmasi pesanan.</small></div></div>
        <div class="kr-step"><span class="kr-n">2</span><div><b>Tunggu Siap Diambil</b><small>Pantau status di halaman Transaksi.</small></div></div>
        <div class="kr-step"><span class="kr-n">3</span><div><b>Ambil &amp; Bayar di Toko</b><small>Bayar tunai (COD) di kasir saat mengambil pesanan.</small></div></div>
      </div>
    </div>
  </aside>
</div>

<script>
  const q=i=>document.getElementById(i), cbs=[...document.querySelectorAll('.cb')], rp=n=>'Rp '+n.toLocaleString('id-ID');
  function hit(){
    const c=cbs.filter(x=>x.checked), t=c.reduce((a,x)=>a+ +x.dataset.sub,0);
    q('itm').textContent=q('cnt').textContent=c.length; q('sub').textContent=q('tot').textContent=rp(t);
    q('all').checked=c.length==cbs.length; q('lanjut').disabled=!c.length;
  }
  cbs.forEach(x=>x.onchange=hit); q('all').onchange=()=>{cbs.forEach(x=>x.checked=q('all').checked);hit()}; hit();
</script>
@endif
@endsection