@extends('admin.layout')
@section('title','Laporan Penjualan')
@section('content')
<h1 class="ad-h">Laporan Penjualan</h1>

<div class="ds-stats ds-one">
  <div class="ds-card"><small>Kategori Aktif</small><b>{{ number_format($kategoriAktif,0,',','.') }}</b></div>
</div>

@include('admin.partials.charts')
@include('admin.partials.aktif')

{{-- Histori penjualan (pesanan selesai) + rekap Excel --}}
<section class="ad-card ch-card">
  <div class="ch-head">
    <div><h2 class="ch-title">Histori Penjualan</h2>
      <p class="ad-sub">{{ $riwayat->count() }} pesanan selesai · Total Rp {{ number_format($riwayat->sum('total_harga'),0,',','.') }}</p></div>
    <form method="GET" class="ad-act">
      <input type="month" name="bulan" value="{{ $bulan }}" class="ad-in ad-auto" onchange="this.form.submit()">
      <a class="ad-btn or" href="{{ route('admin.penjualan.export',['bulan'=>$bulan]) }}">@include('icon',['n'=>'download']) Download Excel</a>
    </form>
  </div>
  <div class="ad-tbl-w">
    <table class="ad-tbl">
      <thead><tr><th>No</th><th>Kode Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Produk</th><th>Item</th><th>Total</th></tr></thead>
      <tbody>
      @forelse($riwayat as $i=>$t)
        <tr>
          <td>{{ $i+1 }}</td>
          <td><b>#{{ $t->kode }}</b></td>
          <td>{{ $t->tanggal_transaksi->locale('id')->translatedFormat('d M Y • H:i') }}</td>
          <td>{{ optional($t->user)->nama_user ?? '-' }}</td>
          <td class="ad-wrapcell">{{ $t->detail->map(fn($d)=>optional($d->produk)->nama_produk.' x'.$d->jumlah)->join(', ') }}</td>
          <td>{{ $t->detail->sum('jumlah') }}</td>
          <td><b>Rp {{ number_format($t->total_harga,0,',','.') }}</b></td>
        </tr>
      @empty
        <tr><td colspan="7" class="ad-empty">Belum ada penjualan selesai pada bulan ini.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>
@endsection