<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nama_user' => 'Admin GoManisGo',
            'no_telepon' => '081234567890',
            'email' => 'admin@gomanisgo.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
