<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // SQLite tidak mendukung MODIFY COLUMN atau stored procedure.
        // Status sudah didefinisikan sebagai string di migration utama.
        // Update data lama jika ada.
        DB::table('transaksi')->where('status', 'pending')->update(['status' => 'diproses']);
        DB::table('transaksi')->where('status', 'dikirim')->update(['status' => 'siap_diambil']);
    }

    public function down()
    {
        // tidak perlu rollback
    }
};
