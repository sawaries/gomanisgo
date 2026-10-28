<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel kategori
        Schema::create('kategori', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->string('nama_kategori', 100);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
        });

        // Tabel produk
        Schema::create('produk', function (Blueprint $table) {
            $table->increments('id_produk');
            $table->unsignedInteger('id_kategori')->nullable();
            $table->string('nama_produk', 150);
            $table->string('sku', 50)->nullable()->unique();
            $table->string('satuan', 30)->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->boolean('status')->default(true);

            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->onDelete('set null');
        });

        // Tabel keranjang
        Schema::create('keranjang', function (Blueprint $table) {
            $table->increments('id_keranjang');
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Tabel detail_keranjang
        Schema::create('detail_keranjang', function (Blueprint $table) {
            $table->unsignedInteger('id_keranjang');
            $table->unsignedInteger('id_produk');
            $table->integer('jumlah')->default(1);

            $table->primary(['id_keranjang', 'id_produk']);
            $table->foreign('id_keranjang')->references('id_keranjang')->on('keranjang')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
        });

        // Tabel transaksi
        Schema::create('transaksi', function (Blueprint $table) {
            $table->increments('id_transaksi');
            $table->unsignedBigInteger('id_user');
            $table->unsignedInteger('id_keranjang')->nullable();
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->string('status', 30)->default('diproses');
            $table->timestamp('tanggal_transaksi')->nullable()->useCurrent();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_keranjang')->references('id_keranjang')->on('keranjang')->onDelete('set null');
        });

        // Tabel detail_transaksi
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->increments('id_detail_transaksi');
            $table->unsignedInteger('id_transaksi');
            $table->unsignedInteger('id_produk');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->integer('jumlah')->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
        });

        // Tabel pembayaran
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->increments('id_pembayaran');
            $table->unsignedInteger('id_transaksi');
            $table->string('metode_pembayaran', 50)->nullable();
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->string('status_pembayaran', 30)->default('menunggu');

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('detail_transaksi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('detail_keranjang');
        Schema::dropIfExists('keranjang');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('kategori');
    }
};
