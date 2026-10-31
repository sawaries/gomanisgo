@extends('layout')
@section('content')
<form method="POST" action="{{ route('register') }}" class="au-card">
  @csrf
  <h1 class="au-title">Daftar Akun</h1>
  <p class="au-sub">Buat akun untuk memesan dan memantau transaksi.</p>
  @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
  <label class="au-lb">Nama</label>
  <input name="nama_user" value="{{ old('nama_user') }}" class="au-in" placeholder="Nama lengkap" required>
  <label class="au-lb">No. Telepon</label>
  <input name="no_telepon" value="{{ old('no_telepon') }}" class="au-in" placeholder="08xxxxxxxxxx" required>
  <label class="au-lb">Email</label>
  <input name="email" type="email" value="{{ old('email') }}" class="au-in" placeholder="nama@email.com" required>
  <label class="au-lb">Password</label>
  <input name="password" type="password" class="au-in pw" placeholder="Password" required>
  <label class="au-chk"><input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')"> Tampilkan password</label>
  <button class="au-btn">Daftar</button>
  <a href="{{ route('login') }}" class="au-link">Sudah punya akun? Masuk</a>
</form>
@endsection