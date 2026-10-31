@extends('layout')
@section('content')
@php
  $langkah=['diproses'=>'Diproses','disiapkan'=>'Disiapkan','siap_diambil'=>'Siap Diambil','selesai'=>'Selesai'];
  $aktif=($hitung['diproses'] ?? 0)+($hitung['disiapkan'] ?? 0)+($hitung['siap_diambil'] ?? 0);
@endphp
<h1 class="kr-title">Profil Saya</h1>
<p class="kr-sub">Kelola data diri dan pantau aktivitas akun ambil di toko</p>

<div class="kr-wrap">
  <div class="kr-col">
    {{-- Identitas --}}
    <section class="kr-card pf-top">
      <div class="pf-head">
        <div class="pf-ava">{{ strtoupper(substr($u->nama_user,0,1)) }}</div>
        <div class="pf-id">
          <h2>{{ $u->nama_user }}</h2>
          <p>{{ $u->email }} • {{ $u->no_telepon }}</p>
        </div>
        <div class="pf-act">
          <button type="button" class="gm-btn" onclick="buka()">Edit Profil</button>
          <form action="{{ route('logout') }}" method="POST">@csrf<button class="gm-btn out">Keluar</button></form>
        </div>
      </div>

      <div class="pf-stats">
        <div class="pf-stat"><small>Pesanan Aktif</small><b>{{ $aktif }}</b></div>
        <div class="pf-stat"><small>Pesanan Selesai</small><b>{{ $hitung['selesai'] ?? 0 }}</b></div>
        <div class="pf-stat"><small>Total Belanja</small><b class="or">Rp {{ number_format($belanja,0,',','.') }}</b></div>
      </div>

      <div class="pf-info">
        <div class="pf-box"><small>Nama Lengkap</small><b>{{ $u->nama_user }}</b></div>
        <div class="pf-box"><small>No. Telepon</small><b>{{ $u->no_telepon }}</b></div>
      </div>
    </section>

    {{-- Pesanan saya --}}
    <section class="kr-card">
      <div class="kr-h"><span>Pesanan Saya</span><a href="{{ route('transaksi.index') }}" class="gm-link">Lihat Semua</a></div>
      <div class="pf-steps">
        @foreach($langkah as $k=>$l)
          <a href="{{ route('transaksi.index',['status'=>$k]) }}" class="pf-step">
            <span class="kr-n">{{ $loop->iteration }}</span><b>{{ $l }}</b><small>{{ $hitung[$k] ?? 0 }} pesanan</small>
          </a>
        @endforeach
      </div>
      @forelse($terakhir as $t)
        <a href="{{ route('transaksi.index') }}" class="pf-ord">
          <i class="pf-mg">MG</i>
          <div><b>Pesanan #{{ $t->kode }}</b><div class="gm-sub">{{ $t->detail->count() }} item • Ambil di toko</div></div>
          <div class="pf-rt"><b class="gm-price">Rp {{ number_format($t->total_harga,0,',','.') }}</b><span class="tx-b {{ $t->status }}">{{ \App\Models\Transaksi::STATUS[$t->status] }}</span></div>
        </a>
      @empty
        <p class="pf-empty">Belum ada pesanan. <a href="{{ route('home') }}#produk" class="gm-link">Mulai belanja</a></p>
      @endforelse
    </section>
  </div>

  {{-- Terakhir dilihat --}}
  <aside class="kr-side">
    <section class="kr-card">
      <div class="kr-h">Terakhir Dilihat</div>
      @forelse($dilihat as $p)
        <a href="{{ route('produk.show',$p->id_produk) }}" class="pf-view">
          <div class="pf-th">@if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div>
          <div><b>{{ $p->nama_produk }}</b><div class="gm-price">Rp {{ number_format($p->harga,0,',','.') }}</div></div>
        </a>
      @empty
        <p class="pf-empty">Belum ada produk yang dilihat.</p>
      @endforelse
    </section>
  </aside>
</div>

{{-- Pop-up Edit Profil --}}
<div class="md-ov" id="md" onclick="if(event.target===this)tutup()">
  <form class="md-box" method="POST" action="{{ route('profil.update') }}">
    @csrf @method('PUT')
    <div class="md-h"><b>Edit Profil</b><button type="button" class="ad-ic" onclick="tutup()">@include('icon',['n'=>'x'])</button></div>
    <div class="md-b">
      @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
      <label class="au-lb">Nama Lengkap</label>
      <input name="nama_user" class="au-in" value="{{ old('nama_user',$u->nama_user) }}" required>
      <label class="au-lb">No. Telepon</label>
      <input name="no_telepon" class="au-in" value="{{ old('no_telepon',$u->no_telepon) }}" required>
      <label class="au-lb">Email</label>
      <input name="email" type="email" class="au-in" value="{{ old('email',$u->email) }}" required>

      <p class="md-sep">Ubah password (opsional)</p>
      <label class="au-lb">Password saat ini</label>
      <input name="password_lama" type="password" class="au-in pw" autocomplete="current-password">
      <label class="au-lb">Password baru</label>
      <input name="password_baru" type="password" class="au-in pw" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
      <label class="au-chk"><input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')"> Tampilkan password</label>
    </div>
    <div class="md-f"><button type="button" class="gm-btn out" onclick="tutup()">Batal</button><button class="gm-btn">Simpan</button></div>
  </form>
</div>

<script>
  const md=document.getElementById('md'), buka=()=>md.classList.add('on'), tutup=()=>md.classList.remove('on');
  document.addEventListener('keydown',e=>{if(e.key==='Escape')tutup()});
  @if($errors->any()) buka(); @endif
</script>
@endsection