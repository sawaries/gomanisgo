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
        <h2>Bergabung Sekarang</h2>
        <p>Buat akun dan nikmati kemudahan berbelanja sembako dengan harga terbaik.</p>
      </div>
    </div>

    {{-- Kanan: Form Register User --}}
    <form method="POST" action="{{ route('register') }}" class="au-split-form">
      @csrf

      <div class="au-form-header">
        <h1>Daftar Akun</h1>
        <p>Buat akun untuk memesan dan memantau transaksi</p>
      </div>

      @if($errors->any())
        <div class="au-alert">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="au-field">
        <label for="nama_user">Nama Lengkap</label>
        <input id="nama_user" name="nama_user" value="{{ old('nama_user') }}" class="au-input" placeholder="Nama lengkap Anda" required autofocus>
      </div>

      <div class="au-field">
        <label for="no_telepon">No. Telepon / WhatsApp</label>
        <input id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" class="au-input" placeholder="08xxxxxxxxxx" required>
      </div>

      <div class="au-field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="au-input" placeholder="nama@email.com" required>
      </div>

      <div class="au-field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="au-input pw" placeholder="Minimal 6 karakter" required>
      </div>

      <div class="au-options">
        <label class="au-checkbox-label">
          <input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')">
          <span>Tampilkan password</span>
        </label>
      </div>

      <button type="submit" class="au-submit-btn">Daftar Sekarang</button>

      <div class="au-divider">
        <span>atau</span>
      </div>

      <div class="au-footer-links">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
      </div>

      <p class="au-legal-note">
        Dengan mendaftar, Anda menyetujui <a href="#">Syarat &amp; Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> GoManisGo.
      </p>
    </form>

  </div>
</div>
@endsection
