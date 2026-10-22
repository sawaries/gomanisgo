<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'sku',
        'satuan',
        'harga',
        'stok',
        'deskripsi',
        'gambar',
        'status'
    ];

    protected $casts = ['status' => 'boolean'];

    public function getGambarUrlAttribute()
    {
        return $this->gambar ? asset('storage/'.$this->gambar) : null;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}