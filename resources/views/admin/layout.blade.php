<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Admin') - GoManisGo</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="ad">
  <header class="ad-top">
    <button class="ad-burger" onclick="sb(true)">@include('icon',['n'=>'menu'])</button>
    <a class="gm-brand" href="{{ route('admin.dashboard') }}"><i>@include('icon',['n'=>'bag'])</i><span>Go<b>ManisGo</b><small>Admin</small></span></a>
    <div class="ad-user">
      <span>{{ auth('admin')->user()->nama_user }}<small>Admin</small></span>
      <div class="ad-ava">{{ strtoupper(substr(auth('admin')->user()->nama_user,0,1)) }}</div>
      <form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="ad-out">Logout</button></form>
    </div>
  </header>

  <div class="ad-wrap">
    <div class="ad-ov sb" id="ovs" onclick="sb(false)"></div>
    <aside class="ad-side" id="side">
      <h6>MENU UTAMA</h6>
      <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'on' : '' }}">@include('icon',['n'=>'home']) Dashboard</a>
      <a href="{{ route('admin.produk.index') }}" class="{{ request()->routeIs('admin.produk.*') ? 'on' : '' }}">@include('icon',['n'=>'box']) Manajemen Produk</a>
      <a href="{{ route('admin.kategori.index') }}" class="{{ request()->routeIs('admin.kategori.*') ? 'on' : '' }}">@include('icon',['n'=>'folder']) Manajemen Kategori</a>
      <a href="{{ route('admin.pesanan.index') }}" class="{{ request()->routeIs('admin.pesanan.*') ? 'on' : '' }}">@include('icon',['n'=>'bag']) Pesanan Masuk</a>
      <a href="{{ route('admin.penjualan.index') }}" class="{{ request()->routeIs('admin.penjualan.*') ? 'on' : '' }}">@include('icon',['n'=>'trend']) Laporan Penjualan</a>
    </aside>

    <main class="ad-main">
      @if(session('success'))<div class="ad-alert">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="ad-alert err">{{ session('error') }}</div>@endif
      @yield('content')
    </main>
  </div>

  <script>
    const g=i=>document.getElementById(i);
    const sb=o=>{g('side').classList.toggle('on',o);g('ovs').classList.toggle('on',o)};
    const dr=o=>{g('ovd').classList.toggle('on',o);g('frm').classList.toggle('on',o)};
    const pv=u=>{g('pvi').hidden=!u;if(u)g('pvi').src=u};
    document.onkeydown=e=>{if(e.key=='Escape'){if(g('frm'))dr(false);sb(false)}};
    const dz=g('dz');
    if(dz){const fi=dz.querySelector('input');
      dz.ondragover=e=>{e.preventDefault();dz.classList.add('drag')};
      dz.ondragleave=()=>dz.classList.remove('drag');
      dz.ondrop=e=>{e.preventDefault();dz.classList.remove('drag');fi.files=e.dataTransfer.files;fi.onchange()}}
  </script>
  @stack('js')
</body>
</html>