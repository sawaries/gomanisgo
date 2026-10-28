<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaksi extends Model
{
    const STATUS = [
        'diproses' => 'Diproses',
        'disiapkan' => 'Disiapkan',
        'siap_diambil' => 'Siap Diambil',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected $table = 'transaksi';

    protected $casts = ['tanggal_transaksi' => 'datetime'];

    protected $primaryKey = 'id_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_keranjang',
        'sumber',
        'nomor_wa',
        'total_harga',
        'status',
        'catatan',
    ];

    public function detail()
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'id_transaksi'
        );
    }

    public function pembayaran()
    {
        return $this->hasOne(
            Pembayaran::class,
            'id_transaksi'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user'
        );
    }

    const AKTIF = ['diproses', 'disiapkan', 'siap_diambil'];

    public function scopeAktif($q)
    {
        return $q->whereIn('status', self::AKTIF);
    }

    // kode pesanan tampilan, contoh: MG-241005-012
    public function getKodeAttribute()
    {
        return 'MG-'.optional($this->tanggal_transaksi)->format('ymd').'-'.str_pad($this->id_transaksi, 3, '0', STR_PAD_LEFT);
    }

    // batalkan pesanan + kembalikan stok (stok dikurangi trigger saat detail dibuat)
    public function batalkan()
    {
        DB::transaction(function () {
            foreach ($this->detail as $d) {
                Produk::where('id_produk', $d->id_produk)->increment('stok', $d->jumlah);
            }
            $this->update(['status' => 'dibatalkan']);
        });
    }
}
