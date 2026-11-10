<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Gudang extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;
    protected $fillable = ['nama_user', 'no_telepon', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
}