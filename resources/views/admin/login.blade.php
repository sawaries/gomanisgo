<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - GoManisGo</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="au-dark">
  <form method="POST" action="{{ route('admin.login') }}" class="au-card">
    @csrf
    <div class="au-brand">Go<b>ManisGo</b></div>
    <h1 class="au-title au-center">Admin Login</h1>
    <p class="au-sub au-center">Masuk untuk mengelola toko.</p>
    @if(session('error'))<div class="au-alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
    <label class="au-lb">Email</label>
    <input name="email" type="email" value="{{ old('email') }}" class="au-in" placeholder="admin@gomanisgo.com" required>
    <label class="au-lb">Password</label>
    <input name="password" type="password" class="au-in pw" placeholder="Password" required>
    <label class="au-chk"><input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')"> Tampilkan password</label>
    <button class="au-btn dark">Masuk</button>
  </form>
</body>
</html>