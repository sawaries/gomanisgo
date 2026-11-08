@extends('layout')
@section('content')
<form method="POST" action="{{ route('resetpassword.send') }}" class="au-card">
  @csrf
  <h1 class="au-title">Lupa Password</h1>
  <p class="au-sub">Masukkan email terdaftar, kami kirim kode OTP untuk reset password.</p>
  @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
  <label class="au-lb">Email</label>
  <input name="email" type="email" value="{{ old('email') }}" class="au-in" placeholder="nama@email.com" required>
  <button class="au-btn">Kirim OTP</button>
  <a href="{{ route('login') }}" class="au-link">Kembali ke login</a>
</form>
@endsection