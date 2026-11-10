@extends('gudang.layout')
@section('title','Dashboard Gudang')
@section('content')
<h1 class="ad-h">Dashboard Gudang</h1>
<p class="ad-sub">Ringkasan pesanan yang perlu disiapkan.</p>

<div class="ds-stats">
  <div class="ds-card"><small>Pesanan Aktif</small><b>{{ $stat['aktif'] }}</b></div>
  <div class="ds-card"><small>Menunggu Diproses</small><b>{{ $stat['menunggu'] }}</b></div>
  <div class="ds-card"><small>Sedang Disiapkan</small><b>{{ $stat['disiapkan'] }}</b></div>
  <div class="ds-card"><small>Siap Diambil</small><b>{{ $stat['siap'] }}</b></div>
</div>

<section class="ad-card ch-card">
  <div class="ch-head">
    <div><h2 class="ch-title">Pesanan Terbaru</h2><p class="ad-sub">{{ $terbaru->count() }} pesanan aktif terbaru</p></div>
    <a class="ad-btn or" href="{{ route('gudang.pesanan.index') }}">Buka Pesanan Masuk</a>
  </div>
  <div class="ad-tbl-w">
    <table class="ad-tbl">
      <thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Status</th><th>Total</th></tr></thead>
      <tbody>
      @forelse($terbaru as $t)
        <tr class="ps-row {{ $t->status=='diproses' ? 'ad-hi' : '' }}" onclick="location.href='{{ route('gudang.pesanan.index',['id'=>$t->id_transaksi]) }}'">
          <td><b>#{{ $t->kode }}</b><div class="ad-mut">{{ $t->tanggal_transaksi->locale('id')->translatedFormat('d M Y • H:i') }}</div></td>
          <td><b>{{ optional($t->user)->nama_user ?? '-' }}</b></td>
          <td><span class="tx-b {{ $t->status }}">{{ \App\Models\Transaksi::STATUS[$t->status] }}</span><div class="ad-mut">{{ $t->detail->count() }} item</div></td>
          <td><b>Rp {{ number_format($t->total_harga,0,',','.') }}</b></td>
        </tr>
      @empty
        <tr><td colspan="4" class="ad-empty">Tidak ada pesanan aktif.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>
@endsection