<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $data = [
            'nama_user' => 'Petugas Gudang',
            'no_telepon' => '081200000000',
            'password' => Hash::make('gudang123'),
            'role' => 'gudang',
        ];
        if (Schema::hasColumn('users', 'created_at')) {
            $data += ['created_at' => now(), 'updated_at' => now()];
        }
        DB::table('users')->updateOrInsert(['email' => 'gudang@gomanisgo.com'], $data);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'gudang@gomanisgo.com')->where('role', 'gudang')->delete();
    }
};