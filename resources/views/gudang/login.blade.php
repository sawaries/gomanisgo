<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Gudang - GoManisGo</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="au-admin-page">

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
        <h2>Panel Gudang</h2>
        <p>Masuk untuk memantau pesanan masuk, menyiapkan barang, dan mengelola stok.</p>
      </div>
    </div>

    {{-- Kanan: Form Login Gudang --}}
    <form method="POST" action="{{ route('gudang.login.submit') }}" class="au-split-form">
      @csrf

      <div class="au-form-header">
        <h1>Login Gudang</h1>
        <p>Masuk untuk mengelola pesanan masuk</p>
      </div>

      @if(session('error'))
        <div class="au-alert">
          {{ session('error') }}
        </div>
      @endif

      @if($errors->any())
        <div class="au-alert">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="au-field">
        <label for="gudang-email">Email</label>
        <input id="gudang-email" name="email" type="email" value="{{ old('email') }}" class="au-input" placeholder="gudang@gomanisgo.com" required autofocus>
      </div>

      <div class="au-field">
        <label for="gudang-password">Password</label>
        <input id="gudang-password" name="password" type="password" class="au-input pw" placeholder="Masukkan password gudang" required>
      </div>

      <div class="au-options">
        <label class="au-checkbox-label">
          <input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')">
          <span>Tampilkan password</span>
        </label>
      </div>

      <button type="submit" class="au-submit-btn">Masuk sebagai Staf Gudang</button>

      <p class="au-legal-note" style="margin-top: 2rem;">
        Area khusus staf gudang dan operasional resmi GoManisGo.
      </p>
    </form>

  </div>

</body>
</html>
