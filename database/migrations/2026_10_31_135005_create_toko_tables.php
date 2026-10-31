<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $t) {
            $t->integer('id_kategori', true);
            $t->string('nama_kategori', 100);
            $t->text('deskripsi')->nullable();
            // kolom 'gambar' ditambah di migration add_detail_to_produk_dan_kategori
        });

        Schema::create('produk', function (Blueprint $t) {
            $t->integer('id_produk', true);
            $t->integer('id_kategori');
            $t->string('nama_produk', 150);
            $t->decimal('harga', 12, 2);
            $t->integer('stok')->default(0);
            // sku, satuan, deskripsi, gambar, status ditambah di migration add_detail_to_produk_dan_kategori
            $t->foreign('id_kategori', 'produk_ibfk_1')->references('id_kategori')->on('kategori');
        });

        Schema::create('keranjang', function (Blueprint $t) {
            $t->integer('id_keranjang', true);
            $t->integer('id_user');
            $t->dateTime('tanggal_dibuat')->nullable()->useCurrent();
            $t->foreign('id_user', 'keranjang_ibfk_1')->references('id_user')->on('users');
        });

        Schema::create('detail_keranjang', function (Blueprint $t) {
            $t->integer('id_keranjang');
            $t->integer('id_produk');
            $t->integer('jumlah');
            $t->primary(['id_keranjang', 'id_produk']);
            $t->foreign('id_keranjang', 'detail_keranjang_ibfk_1')->references('id_keranjang')->on('keranjang');
            $t->foreign('id_produk', 'detail_keranjang_ibfk_2')->references('id_produk')->on('produk');
        });

        Schema::create('transaksi', function (Blueprint $t) {
            $t->integer('id_transaksi', true);
            $t->integer('id_user');
            $t->integer('id_keranjang');
            $t->decimal('total_harga', 12, 2)->default(0);
            // status awal; diubah oleh migration ubah_status_transaksi_dan_prosedur
            $t->enum('status', ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('pending');
            $t->dateTime('tanggal_transaksi')->nullable()->useCurrent();
            $t->foreign('id_user', 'transaksi_ibfk_1')->references('id_user')->on('users');
            $t->foreign('id_keranjang', 'transaksi_ibfk_2')->references('id_keranjang')->on('keranjang');
        });

        Schema::create('detail_transaksi', function (Blueprint $t) {
            $t->integer('id_detail_transaksi', true);
            $t->integer('id_transaksi');
            $t->integer('id_produk');
            $t->decimal('harga_satuan', 12, 2);
            $t->integer('jumlah');
            $t->decimal('subtotal', 12, 2);
            $t->foreign('id_transaksi', 'detail_transaksi_ibfk_1')->references('id_transaksi')->on('transaksi');
            $t->foreign('id_produk', 'detail_transaksi_ibfk_2')->references('id_produk')->on('produk');
        });

        Schema::create('pembayaran', function (Blueprint $t) {
            $t->integer('id_pembayaran', true);
            $t->integer('id_transaksi')->unique();
            $t->enum('metode_pembayaran', ['COD'])->nullable()->default('COD');
            $t->decimal('total_bayar', 12, 2);
            $t->enum('status_pembayaran', ['Belum Dibayar', 'Lunas'])->nullable()->default('Belum Dibayar');
            $t->foreign('id_transaksi', 'pembayaran_ibfk_1')->references('id_transaksi')->on('transaksi');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['pembayaran', 'detail_transaksi', 'transaksi', 'detail_keranjang', 'keranjang', 'produk', 'kategori'] as $tb) {
            Schema::dropIfExists($tb);
        }
        Schema::enableForeignKeyConstraints();
    }
};