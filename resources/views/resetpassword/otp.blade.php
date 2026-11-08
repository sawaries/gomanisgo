@extends('layout')
@section('content')
<form method="POST" action="{{ route('resetpassword.otp.verify') }}" class="au-card">
  @csrf
  <h1 class="au-title">Verifikasi OTP</h1>
  <p class="au-sub">Masukkan 6 digit kode yang dikirim ke {{ $email }}. Berlaku 10 menit.</p>
  @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
  <label class="au-lb">Kode OTP</label>
  <input name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6" class="au-in" placeholder="123456" autocomplete="one-time-code" required autofocus>
  <button class="au-btn">Verifikasi</button>
  <a href="{{ route('resetpassword.email') }}" class="au-link">Kirim ulang OTP / ganti email</a>
</form>
@endsection