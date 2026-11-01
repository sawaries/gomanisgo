<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'nama_user',
        'no_telepon',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
}
