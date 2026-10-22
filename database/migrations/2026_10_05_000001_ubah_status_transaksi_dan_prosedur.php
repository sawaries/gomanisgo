<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(){
        // alur baru: diproses -> disiapkan -> siap_diambil -> selesai (atau dibatalkan)
        DB::statement("ALTER TABLE transaksi MODIFY status ENUM('pending','diproses','dikirim','disiapkan','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'diproses'");
        DB::table('transaksi')->where('status','pending')->update(['status'=>'diproses']);
        DB::table('transaksi')->where('status','dikirim')->update(['status'=>'siap_diambil']);
        DB::statement("ALTER TABLE transaksi MODIFY status ENUM('diproses','disiapkan','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'diproses'");

        // prosedur lama memakai status 'Menunggu Diproses' (tidak ada di enum)
        DB::unprepared('DROP PROCEDURE IF EXISTS buat_transaksi');
        DB::unprepared("CREATE PROCEDURE buat_transaksi(IN p_id_user INT, IN p_id_keranjang INT)
            BEGIN
                INSERT INTO transaksi (id_user, id_keranjang, total_harga, status)
                VALUES (p_id_user, p_id_keranjang, 0, 'diproses');
            END");
    }
    public function down(){
        DB::statement("ALTER TABLE transaksi MODIFY status ENUM('pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'pending'");
    }
};