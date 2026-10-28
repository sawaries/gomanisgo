<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $primaryKey = 'id_keranjang';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
    ];

    public function items()
    {
        return $this->hasMany(
            DetailKeranjang::class,
            'id_keranjang'
        );
    }
}
