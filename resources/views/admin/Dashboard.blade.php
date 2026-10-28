@extends('admin.layout')
@section('title','Dashboard')
@section('content')
<h1 class="ad-h">Dashboard</h1>

<div class="ds-stats">
  <div class="ds-card"><small>Kategori Aktif</small><b>{{ number_format($stat['kategori'],0,',','.') }}</b></div>
  <div class="ds-card"><small>Total Penjualan</small><b>{{ number_format($stat['penjualan'],0,',','.') }}</b></div>
  <div class="ds-card"><small>Produk Aktif</small><b>{{ number_format($stat['produk'],0,',','.') }}</b></div>
  <div class="ds-card"><small>Total Pendapatan</small><b>Rp. {{ number_format($stat['pendapatan'],0,',','.') }}</b></div>
</div>

@include('admin.partials.charts')
@include('admin.partials.aktif')
@endsection