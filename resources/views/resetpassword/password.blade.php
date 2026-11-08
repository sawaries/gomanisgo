@extends('layout')
@section('content')
<form method="POST" action="{{ route('resetpassword.update') }}" class="au-card">
  @csrf
  <h1 class="au-title">Password Baru</h1>
  <p class="au-sub">OTP terverifikasi. Buat password baru Anda.</p>
  @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
  <label class="au-lb">Password Baru</label>
  <input name="password" type="password" class="au-in pw" placeholder="Minimal 6 karakter" required>
  <label class="au-lb">Konfirmasi Password</label>
  <input name="password_confirmation" type="password" class="au-in pw" placeholder="Ulangi password" required>
  <label class="au-chk"><input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')"> Tampilkan password</label>
  <button class="au-btn">Simpan Password</button>
</form>
@endsection