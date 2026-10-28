<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $primaryKey = 'id_detail_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'id_produk',
        'nama_produk_snapshot',
        'harga_satuan',
        'jumlah',
        'subtotal',
    ];

    public function produk()
    {
        return $this->belongsTo(
            Produk::class,
            'id_produk'
        );
    }
}
