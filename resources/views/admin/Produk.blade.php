@extends('admin.layout')
@section('title','Manajemen Produk')
@section('content')
<h1 class="ad-h">Manajemen Produk</h1>
<p class="ad-sub">Kelola stok, harga, dan informasi katalog produk Sumber Manis.</p>

<div class="ad-stats">
  <div class="ad-stat"><div><small>Total Produk</small><b>{{ $stat['total'] }}</b></div><i>@include('icon',['n'=>'box'])</i></div>
  <div class="ad-stat"><div><small>Produk Aktif</small><b class="ok">{{ $stat['aktif'] }}</b></div><i class="ok">@include('icon',['n'=>'circle-check'])</i></div>
  <div class="ad-stat"><div><small>Stok Menipis</small><b class="warn">{{ $stat['menipis'] }}</b></div><i class="warn">@include('icon',['n'=>'alert'])</i></div>
  <div class="ad-stat"><div><small>Kategori</small><b>{{ $stat['kategori'] }}</b></div><i>@include('icon',['n'=>'folder'])</i></div>
</div>

<div class="ad-card">
  <form method="GET" class="ad-bar">
    <input class="ad-in" name="q" value="{{ request('q') }}" placeholder="Cari nama produk...">
    <select class="ad-in ad-in-sm" name="kategori" onchange="this.form.submit()">
      <option value="">Semua Kategori</option>
      @foreach($kategori as $k)<option value="{{ $k->id_kategori }}" @selected(request('kategori')==$k->id_kategori)>{{ $k->nama_kategori }}</option>@endforeach
    </select>
    <button type="button" class="ad-btn ad-end" onclick="buka()">@include('icon',['n'=>'plus']) Tambah Produk</button>
  </form>

  <form id="bulk" method="POST" action="{{ route('admin.produk.bulk') }}">@csrf</form>
  <div class="ad-tbl-w">
    <table class="ad-tbl">
      <thead><tr>
        <th><input type="checkbox" onchange="document.querySelectorAll('.cb').forEach(c=>c.checked=this.checked)"></th>
        <th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      @forelse($produk as $p)
        <tr>
          <td><input type="checkbox" class="cb" name="ids[]" value="{{ $p->id_produk }}" form="bulk"></td>
          <td><div class="ad-th">@if($p->gambar_url)<img src="{{ $p->gambar_url }}" alt="">@else @include('icon',['n'=>'image']) @endif</div></td>
          <td><b>{{ $p->nama_produk }}</b><div class="ad-mut">{{ $p->satuan }}</div></td>
          <td>{{ optional($p->kategori)->nama_kategori }}</td>
          <td><b>Rp {{ number_format($p->harga,0,',','.') }}</b></td>
          <td><span class="ad-badge {{ $p->stok<=10 ? 'low' : '' }}">{{ $p->stok }}</span></td>
          <td><span class="ad-badge {{ $p->status ? '' : 'off' }}">{{ $p->status ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td>
            <div class="ad-act">
              <button type="button" class="ad-ic" onclick="buka(JSON.parse(this.dataset.p))"
                data-p="{{ json_encode($p->only(['id_produk','id_kategori','nama_produk','satuan','harga','stok','deskripsi','status'])+['gambar_url'=>$p->gambar_url]) }}">@include('icon',['n'=>'pencil'])</button>
              <form method="POST" action="{{ route('admin.produk.destroy',$p->id_produk) }}" onsubmit="return confirm('Hapus produk ini?')">
                @csrf @method('DELETE')<button class="ad-ic red">@include('icon',['n'=>'trash'])</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="ad-empty">Belum ada produk.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="ad-foot">
    <div class="ad-act">
      <button form="bulk" name="aksi" value="hapus" class="ad-btn red" onclick="return confirm('Hapus produk terpilih?')">@include('icon',['n'=>'trash']) Hapus Terpilih</button>
      <select form="bulk" name="aksi" class="ad-in ad-auto" onchange="if(this.value)this.form.submit()">
        <option value="">Ubah Status</option><option value="aktif">Aktifkan</option><option value="nonaktif">Nonaktifkan</option>
      </select>
    </div>
    <div>{{ $produk->links() }}</div>
  </div>
</div>

{{-- Drawer tambah / edit --}}
<div class="ad-ov" id="ovd" onclick="dr(false)"></div>
<form class="ad-dr" id="frm" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="_method" id="mt" value="PUT" disabled>
  <input type="hidden" name="_id" id="pid" value="{{ old('_id') }}">
  <div class="ad-dr-h"><span id="ttl">Tambah Produk Baru</span><button type="button" class="ad-ic" onclick="dr(false)">@include('icon',['n'=>'x'])</button></div>
  <div class="ad-dr-b">
    @if($errors->any())<div class="ad-alert err">{{ $errors->first() }}</div>@endif
    <label class="ad-lb">Nama Produk *</label>
    <input class="ad-in" name="nama_produk" value="{{ old('nama_produk') }}" placeholder="Contoh: Gula Pasir Putih 1kg" required>
    <div class="ad-2">
      <div><label class="ad-lb">Kategori *</label>
        <select class="ad-in" name="id_kategori" required><option value="">Pilih</option>
          @foreach($kategori as $k)<option value="{{ $k->id_kategori }}" @selected(old('id_kategori')==$k->id_kategori)>{{ $k->nama_kategori }}</option>@endforeach
        </select></div>
      <div><label class="ad-lb">Satuan *</label>
        <select class="ad-in" name="satuan" required><option value="">Pilih</option>
          @foreach(['Pcs','Dus','Kg','Karton','Liter','Pack','Sak'] as $s)<option @selected(old('satuan')==$s)>{{ $s }}</option>@endforeach
        </select></div>
      <div><label class="ad-lb">Harga (Rp) *</label><input class="ad-in" type="number" min="0" name="harga" value="{{ old('harga') }}" placeholder="Harga jual grosir" required></div>
      <div><label class="ad-lb">Jumlah Stok *</label><input class="ad-in" type="number" min="0" name="stok" value="{{ old('stok') }}" placeholder="0" required></div>
    </div>
    <label class="ad-lb">Deskripsi Produk</label>
    <textarea class="ad-in" name="deskripsi" rows="3" placeholder="Detail deskripsi produk...">{{ old('deskripsi') }}</textarea>
    <label class="ad-lb">Foto Produk</label>
    <label class="ad-drop" id="dz">
      @include('icon',['n'=>'image'])
      <img id="pvi" hidden alt="">
      <span>Pilih berkas atau tarik ke sini</span>
      <small>Format PNG, JPG maks. 2MB</small>
      <input type="file" name="gambar" accept="image/png,image/jpeg" hidden onchange="pv(this.files[0]?URL.createObjectURL(this.files[0]):'')">
    </label>
    <div class="ad-row ad-mt">
      <div><b class="ad-sm">Status Penjualan</b><div class="ad-mut">Produk dapat dibeli jika aktif</div></div>
      <input type="hidden" name="status" value="0"><input type="checkbox" class="ad-sw" name="status" value="1" id="st" checked>
    </div>
  </div>
  <div class="ad-dr-f"><button type="button" class="ad-btn out" onclick="dr(false)">Batal</button><button class="ad-btn">@include('icon',['n'=>'check']) Simpan Produk</button></div>
</form>
@endsection

@push('js')
<script>
const f=g('frm'),B="{{ url('admin/produk') }}";
function buka(p,keep){
  const id=p?p.id_produk:(keep?g('pid').value:'');
  f.action=id?B+'/'+id:B; g('mt').disabled=!id; g('pid').value=id||'';
  g('ttl').textContent=id?'Edit Produk':'Tambah Produk Baru';
  if(!keep){
    ['nama_produk','id_kategori','satuan','harga','stok','deskripsi'].forEach(n=>f[n].value=p?(p[n]??''):'');
    g('st').checked=p?!!+p.status:true; f.gambar.value=''; pv(p&&p.gambar_url||'');
  }
  dr(true);
}
@if($errors->any()) buka(null,true); @endif
</script>
@endpush