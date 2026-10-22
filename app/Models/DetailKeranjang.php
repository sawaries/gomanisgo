<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailKeranjang extends Model
{
    protected $table = 'detail_keranjang';
    public $incrementing = false;

    protected $primaryKey = 'id_keranjang';

    public $timestamps = false;

    protected $fillable = [
        'id_keranjang',
        'id_produk',
        'jumlah',
    ];

    public function produk()
    {
        return $this->belongsTo(
            Produk::class,
            'id_produk'
        );
    }
}