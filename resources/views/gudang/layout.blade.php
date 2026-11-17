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
    <a class="gm-brand" href="{{ route('gudang.dashboard') }}"><img src="{{ asset('images/logo1.png') }}" width="45" alt="GoManisGo" class="gm-logo"><span>Go<b>ManisGo</b><small>Gudang</small></span></a>
        <div class="ad-user">
          @php $gu = auth('gudang')->user() ?? auth('admin')->user(); @endphp
          <span>{{ $gu->nama_user }}<small>{{ auth('gudang')->check() ? 'Petugas Gudang' : 'Admin' }}</small></span>
          <div class="ad-ava">{{ strtoupper(substr($gu->nama_user,0,1)) }}</div>
          @if(auth('gudang')->check())
            <form action="{{ route('gudang.logout') }}" method="POST">@csrf<button class="ad-out">Logout</button></form>
          @else
          @endif
        </div>
  </header>

  <div class="ad-wrap">
    <div class="ad-ov sb" id="ovs" onclick="sb(false)"></div>
    <aside class="ad-side" id="side">
      <h6>MENU GUDANG</h6>
      <a href="{{ route('gudang.dashboard') }}" class="{{ request()->routeIs('gudang.dashboard') ? 'on' : '' }}">@include('icon',['n'=>'home']) Dashboard</a>
      <a href="{{ route('gudang.pesanan.index') }}" class="{{ request()->routeIs('gudang.pesanan.*') ? 'on' : '' }}">@include('icon',['n'=>'bag']) Pesanan Masuk</a>
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
    const pv=(u,n)=>{g('pvi').hidden=!u;if(u)g('pvi').src=u;g('dz').classList.toggle('ok',!!u);g('pvn').hidden=!u;g('pvn').textContent=u?(n||''):''};
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