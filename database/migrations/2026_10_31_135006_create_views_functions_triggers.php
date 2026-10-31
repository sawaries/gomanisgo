<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        // ---- Views ----
        DB::unprepared("CREATE OR REPLACE VIEW view_produk_kategori AS
            SELECT p.id_produk, p.nama_produk, k.nama_kategori, p.harga, p.stok
            FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori");

        DB::unprepared("CREATE OR REPLACE VIEW view_keranjang_user AS
            SELECT k.id_keranjang, u.nama_user, p.nama_produk, dk.jumlah, p.harga, (dk.jumlah * p.harga) AS subtotal
            FROM keranjang k
            JOIN users u ON k.id_user = u.id_user
            JOIN detail_keranjang dk ON k.id_keranjang = dk.id_keranjang
            JOIN produk p ON dk.id_produk = p.id_produk");

        DB::unprepared("CREATE OR REPLACE VIEW view_detail_transaksi AS
            SELECT t.id_transaksi, u.nama_user, p.nama_produk, dt.jumlah, dt.harga_satuan, dt.subtotal,
                   t.total_harga, t.status AS status_transaksi, pb.metode_pembayaran, pb.status_pembayaran
            FROM transaksi t
            JOIN users u ON t.id_user = u.id_user
            JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
            JOIN produk p ON dt.id_produk = p.id_produk
            JOIN pembayaran pb ON t.id_transaksi = pb.id_transaksi");

        // ---- Procedures (buat_transaksi dibuat di migration ubah_status_transaksi_dan_prosedur) ----
        DB::unprepared('DROP PROCEDURE IF EXISTS tambah_produk');
        DB::unprepared("CREATE PROCEDURE tambah_produk(IN p_id_kategori INT, IN p_nama_produk VARCHAR(150), IN p_harga DECIMAL(12,2), IN p_stok INT)
            BEGIN
                INSERT INTO produk (id_kategori, nama_produk, harga, stok)
                VALUES (p_id_kategori, p_nama_produk, p_harga, p_stok);
            END");

        DB::unprepared('DROP PROCEDURE IF EXISTS tambah_user');
        DB::unprepared("CREATE PROCEDURE tambah_user(IN p_nama VARCHAR(100), IN p_no_telepon VARCHAR(20), IN p_email VARCHAR(100))
            BEGIN
                INSERT INTO users (nama_user, no_telepon, email)
                VALUES (p_nama, p_no_telepon, p_email);
            END");

        // ---- Functions ----
        DB::unprepared('DROP FUNCTION IF EXISTS hitung_subtotal');
        DB::unprepared("CREATE FUNCTION hitung_subtotal(p_id_produk INT, p_jumlah INT) RETURNS DECIMAL(12,2) DETERMINISTIC
            BEGIN
                DECLARE v_harga DECIMAL(12,2);
                SELECT harga INTO v_harga FROM produk WHERE id_produk = p_id_produk;
                RETURN v_harga * p_jumlah;
            END");

        DB::unprepared('DROP FUNCTION IF EXISTS hitung_total_transaksi');
        DB::unprepared("CREATE FUNCTION hitung_total_transaksi(p_id_transaksi INT) RETURNS DECIMAL(12,2) DETERMINISTIC
            BEGIN
                DECLARE v_total DECIMAL(12,2);
                SELECT COALESCE(SUM(subtotal), 0) INTO v_total FROM detail_transaksi WHERE id_transaksi = p_id_transaksi;
                RETURN v_total;
            END");

        // ---- Triggers ----
        foreach (['trigger_kurangi_stok', 'trigger_update_total', 'trigger_update_status'] as $trg) {
            DB::unprepared("DROP TRIGGER IF EXISTS $trg");
        }
        DB::unprepared("CREATE TRIGGER trigger_kurangi_stok AFTER INSERT ON detail_transaksi FOR EACH ROW
            BEGIN
                UPDATE produk SET stok = stok - NEW.jumlah WHERE id_produk = NEW.id_produk;
            END");

        DB::unprepared("CREATE TRIGGER trigger_update_total AFTER INSERT ON detail_transaksi FOR EACH ROW
            BEGIN
                UPDATE transaksi
                SET total_harga = (SELECT SUM(subtotal) FROM detail_transaksi WHERE id_transaksi = NEW.id_transaksi)
                WHERE id_transaksi = NEW.id_transaksi;
            END");

        DB::unprepared("CREATE TRIGGER trigger_update_status AFTER UPDATE ON pembayaran FOR EACH ROW
            BEGIN
                IF NEW.status_pembayaran = 'Lunas' THEN
                    UPDATE transaksi SET status = 'selesai' WHERE id_transaksi = NEW.id_transaksi;
                END IF;
            END");
    }

    public function down(): void
    {
        foreach (['trigger_kurangi_stok', 'trigger_update_total', 'trigger_update_status'] as $trg) {
            DB::unprepared("DROP TRIGGER IF EXISTS $trg");
        }
        DB::unprepared('DROP FUNCTION IF EXISTS hitung_subtotal');
        DB::unprepared('DROP FUNCTION IF EXISTS hitung_total_transaksi');
        DB::unprepared('DROP PROCEDURE IF EXISTS tambah_produk');
        DB::unprepared('DROP PROCEDURE IF EXISTS tambah_user');
        DB::unprepared('DROP VIEW IF EXISTS view_detail_transaksi');
        DB::unprepared('DROP VIEW IF EXISTS view_keranjang_user');
        DB::unprepared('DROP VIEW IF EXISTS view_produk_kategori');
    }
};