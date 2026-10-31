@extends('layout')
@section('content')
<form method="POST" action="{{ route('login') }}" class="au-card">
  @csrf
  <h1 class="au-title">Masuk</h1>
  <p class="au-sub">Masuk untuk mulai berbelanja di GoManisGo.</p>
  @if($errors->any())<div class="au-alert">{{ $errors->first() }}</div>@endif
  <label class="au-lb">Email</label>
  <input name="email" type="email" value="{{ old('email') }}" class="au-in" placeholder="nama@email.com" required>
  <label class="au-lb">Password</label>
  <input name="password" type="password" class="au-in pw" placeholder="Password" required>
  <label class="au-chk"><input type="checkbox" onchange="this.form.querySelectorAll('.pw').forEach(i=>i.type=this.checked?'text':'password')"> Tampilkan password</label>
  <button class="au-btn">Masuk</button>
  <a href="{{ route('register') }}" class="au-link">Belum punya akun? Daftar</a>
</form>
@endsection