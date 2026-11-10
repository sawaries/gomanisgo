<section class="ad-card ch-card">
  <div class="ch-head">
    <div><h2 class="ch-title">Daftar Pesanan Aktif</h2>
      <p class="ad-sub">{{ $L['aktif']->count() }} pesanan terbaru yang perlu dipantau dan diproses</p></div>
    <span class="ad-badge or">{{ $L['aktifJml'] }} Pesanan</span>
  </div>
  <div class="ad-tbl-w">
    <table class="ad-tbl">
      <thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Status</th><th>Total</th></tr></thead>
      @forelse($L['aktif'] as $t)
        <tr class="{{ $t->status=='diproses' ? 'ad-hi' : '' }}">
          <td><b>#{{ $t->kode }}</b><div class="ad-mut">{{ $t->tanggal_transaksi->locale('id')->translatedFormat('d M Y • H:i') }}</div></td>
          <td><b>{{ optional($t->user)->nama_user ?? '-' }}</b><div class="ad-mut">{{ optional($t->user)->email }}</div></td>
          <td><span class="tx-b {{ $t->status }}">{{ \App\Models\Transaksi::STATUS[$t->status] }}</span><div class="ad-mut">{{ $t->detail->count() }} item</div></td>
          <td><b>Rp {{ number_format($t->total_harga,0,',','.') }}</b></td>
        </tr>
      @empty
        <tr><td colspan="4" class="ad-empty">Tidak ada pesanan aktif.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>