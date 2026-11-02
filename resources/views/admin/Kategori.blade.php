@extends('admin.layout')
@section('title','Manajemen Kategori')
@section('content')
<h1 class="ad-h">Manajemen Kategori</h1>
<p class="ad-sub">Kelompokkan produk agar mudah dicari pelanggan.</p>

<div class="ad-card">
  <div class="ad-bar"><button type="button" class="ad-btn ad-end" onclick="buka()">@include('icon',['n'=>'plus']) Tambah Kategori</button></div>
  <div class="ad-tbl-w">
    <table class="ad-tbl">
      <thead><tr><th>Nama Kategori</th><th>Deskripsi</th><th>Produk</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      @forelse($kategori as $k)
        <tr>
          <td><b>{{ $k->nama_kategori }}</b></td>
          <td class="ad-wrapcell">{{ $k->deskripsi ?: '-' }}</td>
          <td><span class="ad-badge off">{{ $k->produk_count }}</span></td>
          <td>
            <form method="POST" action="{{ route('admin.kategori.toggle',$k->id_kategori) }}">@csrf @method('PUT')
              <input type="checkbox" class="ad-sw" @checked($k->status) onchange="this.form.submit()" title="Aktif / Nonaktif">
            </form>
          </td>
          <td>
            <div class="ad-act">
              <button type="button" class="ad-ic" onclick="buka(JSON.parse(this.dataset.p))"
                data-p="{{ json_encode($k->only(['id_kategori','nama_kategori','deskripsi','status'])) }}">@include('icon',['n'=>'pencil'])</button>
              <form method="POST" action="{{ route('admin.kategori.destroy',$k->id_kategori) }}" onsubmit="return confirm('Hapus kategori ini?')">
                @csrf @method('DELETE')<button class="ad-ic red">@include('icon',['n'=>'trash'])</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="ad-empty">Belum ada kategori.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="ad-ov" id="ovd" onclick="dr(false)"></div>
<form class="ad-dr" id="frm" method="POST">
  @csrf
  <input type="hidden" name="_method" id="mt" value="PUT" disabled>
  <input type="hidden" name="_id" id="pid" value="{{ old('_id') }}">
  <div class="ad-dr-h"><span id="ttl">Tambah Kategori Baru</span><button type="button" class="ad-ic" onclick="dr(false)">@include('icon',['n'=>'x'])</button></div>
  <div class="ad-dr-b">
    @if($errors->any())<div class="ad-alert err">{{ $errors->first() }}</div>@endif
    <label class="ad-lb">Nama Kategori *</label>
    <input class="ad-in" name="nama_kategori" value="{{ old('nama_kategori') }}" placeholder="Contoh: Gula" required>
    <label class="ad-lb">Deskripsi</label>
    <textarea class="ad-in" name="deskripsi" rows="3" placeholder="Deskripsi singkat kategori...">{{ old('deskripsi') }}</textarea>
    <div class="ad-row ad-mt">
      <div><b class="ad-sm">Status Kategori</b><div class="ad-mut">Kategori tampil di toko jika aktif</div></div>
      <input type="hidden" name="status" value="0"><input type="checkbox" class="ad-sw" name="status" value="1" id="st" checked>
    </div>
  </div>
  <div class="ad-dr-f"><button type="button" class="ad-btn out" onclick="dr(false)">Batal</button><button class="ad-btn">@include('icon',['n'=>'check']) Simpan</button></div>
</form>
@endsection

@push('js')
<script>
const f=g('frm'),B="{{ url('admin/kategori') }}";
function buka(p,keep){
  const id=p?p.id_kategori:(keep?g('pid').value:'');
  f.action=id?B+'/'+id:B; g('mt').disabled=!id; g('pid').value=id||'';
  g('ttl').textContent=id?'Edit Kategori':'Tambah Kategori Baru';
  if(!keep){
    ['nama_kategori','deskripsi'].forEach(n=>f[n].value=p?(p[n]??''):'');
    g('st').checked=p?!!+p.status:true;
  }
  dr(true);
}
@if($errors->any()) buka(null,true); @endif
</script>
@endpush