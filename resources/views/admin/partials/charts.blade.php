@php
  $tot=array_column($L['bulan'],'total');
  $top=max($tot);
  $unit=$top>=1000000 ? 1000000 : 1000;
  $mx=max(1,$top/$unit);
  $pow=10**floor(log10($mx));
  $nice=ceil($mx/$pow)*$pow;
  $W=640; $H=300; $kiri=50; $kanan=20; $atas=34; $bawah=44;
  $cw=$W-$kiri-$kanan; $chh=$H-$atas-$bawah;
  $pts=[];
  foreach($tot as $i=>$v){ $pts[]=[round($kiri+$cw*($i+.5)/6,1), round($atas+$chh*(1-($v/$unit)/$nice),1)]; }
@endphp

{{-- Penjualan Bulanan --}}
<section class="ad-card ch-card">
  <div class="ch-head"><div><h2 class="ch-title">Penjualan Bulanan</h2><p class="ad-sub">{{ $L['periode'] }}</p></div></div>
  <div class="ch-kpis">
    <div class="ch-kpi"><small>Total penjualan</small><b>{{ \App\Support\Laporan::singkat($L['total']) }}</b></div>
    <div class="ch-kpi"><small>Pertumbuhan {{ $L['nama_akhir'] }} terhadap {{ $L['nama_lalu'] }}</small>
      @if(!is_null($L['tumbuh']))
        <b class="blue">{{ $L['tumbuh']>=0 ? '+' : '-' }}{{ number_format(abs($L['tumbuh']),1,',','.') }}%</b>
        <em>{{ $L['selisih']>=0 ? '+' : '-' }}{{ \App\Support\Laporan::singkat(abs($L['selisih'])) }}</em>
      @else <b class="blue">-</b><em>belum ada data bulan sebelumnya</em> @endif
    </div>
  </div>
  <div class="ch-meta"><span>Penjualan (Rp {{ $unit==1000000 ? 'juta' : 'ribu' }})</span><span class="ch-legend">Penjualan</span></div>
  <svg class="ch-svg" viewBox="0 0 {{ $W }} {{ $H }}" role="img" aria-label="Grafik penjualan bulanan">
    @for($k=0;$k<=5;$k++)
      @php $yy=round($atas+$chh*(1-$k/5),1); @endphp
      <line class="ch-grid" x1="{{ $kiri }}" y1="{{ $yy }}" x2="{{ $W-$kanan }}" y2="{{ $yy }}"/>
      <text class="ch-t ch-y" x="{{ $kiri-8 }}" y="{{ $yy+4 }}">{{ \App\Support\Laporan::angka($nice*$k/5) }}</text>
    @endfor
    <polyline class="ch-line" points="{{ collect($pts)->map(fn($p)=>$p[0].','.$p[1])->implode(' ') }}"/>
    @foreach($pts as $i=>$p)
      <circle class="ch-dot" cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="4"/>
      <text class="ch-v" x="{{ $p[0] }}" y="{{ $p[1]-11 }}">{{ \App\Support\Laporan::singkat($tot[$i]) }}</text>
      <text class="ch-t ch-x" x="{{ $p[0] }}" y="{{ $H-22 }}">{{ $L['bulan'][$i]['nama'] }}</text>
    @endforeach
    <text class="ch-t ch-x" x="{{ $W/2 }}" y="{{ $H-4 }}">Bulan · {{ now()->year }}</text>
  </svg>
</section>

{{-- Kategori terlaris & Produk terlaris --}}
@foreach([['Kategori Terlaris','kategori','Kategori tertinggi'],['Produk Terlaris','produk','Produk tertinggi']] as [$judul,$key,$tertinggi])
  @php $items=$L[$key]; $skala=max(1,ceil(($items->max('total') ?? 1)*1.15)); $first=$items->first(); @endphp
  <section class="ad-card ch-card">
    <div class="ch-head"><div><h2 class="ch-title">{{ $judul }}</h2><p class="ad-sub">{{ $L['periode'] }}</p></div></div>
    <div class="ch-kpis">
      <div class="ch-kpi"><small>Total produk terjual</small><b>{{ number_format($L['pcs'],0,',','.') }}</b><em>pcs</em></div>
      <div class="ch-kpi"><small>{{ $tertinggi }}</small><b class="blue">{{ $first->nama ?? '-' }}</b>@if($first)<em>{{ (int)$first->total }} pcs</em>@endif</div>
    </div>
    <div class="ch-meta"><span>Jumlah produk terjual (unit)</span><span>Tertinggi ke terendah</span></div>
    <div class="ch-bars">
      @forelse($items as $it)
        <div class="ch-row">
          <span class="ch-nm">{{ $it->nama }}</span>
          <progress class="ch-bar" value="{{ (int)$it->total }}" max="{{ $skala }}"></progress>
          <span><b>{{ (int)$it->total }}</b> <small>pcs</small></span>
        </div>
      @empty
        <p class="ad-mut">Belum ada penjualan selesai pada periode ini.</p>
      @endforelse
    </div>
  </section>
@endforeach