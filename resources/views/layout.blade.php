<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GoManisGo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark gm-nav sticky-top">
    <div class="container-xl">
      <a class="gm-brand" href="{{ route('home') }}"><i>@include('icon',['n'=>'bag'])</i><span>Go<b>ManisGo</b></span></a>
      <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>

      <div class="collapse navbar-collapse" id="menu">
        @php $jml = auth()->check() ? optional(\App\Models\Keranjang::where('id_user',auth()->id())->withCount('items')->first())->items_count : 0; @endphp
        <ul class="navbar-nav mx-lg-auto my-2 my-lg-0 gap-lg-1">
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#kategori">Kategori</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#produk">Produk</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('transaksi.*') ? 'active' : '' }}" href="{{ auth()->check() ? route('transaksi.index') : route('login') }}">Transaksi</a></li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('keranjang.*') ? 'active' : '' }}" href="{{ auth()->check() ? route('keranjang.index') : route('login') }}">
              Keranjang @if($jml)<span class="gm-badge">{{ $jml }}</span>@endif
            </a>
          </li>
        </ul>

        <form action="{{ route('home') }}" class="gm-search me-lg-3 my-2 my-lg-0">
          @include('icon',['n'=>'search'])
          <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari produk, kategori...">
        </form>

        @auth
          <div class="dropdown">
            <a class="gm-prof dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <span class="gm-ava">{{ strtoupper(substr(auth()->user()->nama_user,0,1)) }}</span>
              <span class="lh-1">{{ auth()->user()->nama_user }}<small>User</small></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
              <li><a class="dropdown-item" href="{{ route('keranjang.index') }}">Keranjang Saya</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><form action="{{ route('logout') }}" method="POST">@csrf<button class="dropdown-item">Logout</button></form></li>
            </ul>
          </div>
        @else
          <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-sm text-white" style="background:var(--color-coklat)">Daftar</a>
          </div>
        @endauth
      </div>
    </div>
  </nav>

  <main class="container-xl py-3 py-md-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @yield('content')
  </main>

  <footer class="gm-footer py-3 mt-4">
    <div class="container-xl d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-center">
      <div><b style="color:var(--orange)">GoManisGo</b> · Mitra Terpercaya Kebutuhan Pokok Anda</div>
      <div class="d-flex gap-3"><a href="#">Hubungi Kami</a><a href="#">Syarat &amp; Ketentuan</a><a href="#">Kebijakan Privasi</a></div>
    </div>
    <div class="container-xl text-center text-md-start mt-2">© {{ date('Y') }} Sumber Manis. All rights reserved.</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @auth
    @include('partials.chatbot')
  @endauth
</body>
</html>