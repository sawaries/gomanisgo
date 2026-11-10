<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - GoManisGo</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="au-admin-page">

  <div class="au-split-card">

    {{-- Kiri: Animated Brown/Amber Glowing Gradient --}}
    <div class="au-split-visual">
      <div class="au-brand-pill">
        <img src="{{ asset('images/Logo1.png') }}" alt="GoManisGo">
        <span>GoManisGo</span>
      </div>

      <div class="au-visual-text">
        <h2>Admin Panel</h2>
        <p>Masuk untuk mengelola produk, transaksi, dan operasional toko GoManisGo.</p>
      </div>
    </div>

    {{-- Kanan: Form Login Admin --}}
    <form method="POST" action="{{ route('admin.login') }}" class="au-split-form">
      @csrf

      <div class="au-form-header">
        <h1>Admin Login</h1>
        <p>Masukkan kredensial untuk mengelola toko</p>
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
        <label for="admin-email">Email</label>
        <input id="admin-email" name="email" type="email" value="{{ old('email') }}" class="au-input" placeholder="admin@gomanisgo.com" required autofocus>
      </div>

      <div class="au-field">
        <label for="admin-password">Password</label>
        <input id="admin-password" name="password" type="password" class="au-input pw" placeholder="Masukkan password admin" required>
      </div>

      <div class="au-options">
        <label class="au-checkbox-label">
          <input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')">
          <span>Tampilkan password</span>
        </label>
      </div>

      <button type="submit" class="au-submit-btn">Masuk sebagai Admin</button>

      <p class="au-legal-note" style="margin-top: 2rem;">
        Area khusus administrator resmi GoManisGo. Akses tanpa izin tidak diperkenankan.
      </p>
    </form>

  </div>

</body>
</html>
