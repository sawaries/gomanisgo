@extends('layout')
@section('content')
<div class="au-split-wrap">
  <div class="au-split-card">

    {{-- Kiri: Animated Brown/Amber Glowing Gradient --}}
    <div class="au-split-visual">
      <div class="au-brand-wrap">
        <div class="au-brand-icon">
          <img src="{{ asset('images/Logo1.png') }}" alt="GoManisGo">
        </div>
        <span class="au-brand-text">GoManisGo</span>
      </div>

      <div class="au-visual-text">
        <h2>Hello, Welcome</h2>
        <p>Nikmati kemudahan berbelanja kebutuhan pokok dan sembako dengan harga terbaik.</p>
      </div>
    </div>

    {{-- Kanan: Form Login User --}}
    <form method="POST" action="{{ route('login') }}" class="au-split-form">
      @csrf

      <div class="au-form-header">
        <h1>Masuk ke Akun</h1>
        <p>Masuk untuk mulai berbelanja di GoManisGo</p>
      </div>

      @if($errors->any())
        <div class="au-alert">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="au-field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="au-input" placeholder="nama@email.com" required autofocus>
      </div>

      <div class="au-field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="au-input pw" placeholder="Masukkan password" required>
      </div>

      <div class="au-options">
        <label class="au-checkbox-label">
          <input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')">
          <span>Tampilkan password</span>
        </label>
        <a href="{{ route('resetpassword.email') }}" class="au-forgot-link">Lupa password?</a>
      </div>

      <button type="submit" class="au-submit-btn">Masuk</button>

      <div class="au-divider">
        <span>atau</span>
      </div>

      <div class="au-footer-links">
        Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
      </div>

      <p class="au-legal-note">
        Dengan melanjutkan, Anda menyetujui <a href="#">Syarat &amp; Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> GoManisGo.
      </p>
    </form>

  </div>
</div>
@endsection
