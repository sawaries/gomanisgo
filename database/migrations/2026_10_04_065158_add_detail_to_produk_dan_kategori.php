<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(){
        Schema::table('produk', function (Blueprint $t) {
            if(!Schema::hasColumn('produk','sku')) $t->string('sku',50)->nullable()->unique();
            if(!Schema::hasColumn('produk','satuan')) $t->string('satuan',30)->nullable();
            if(!Schema::hasColumn('produk','deskripsi')) $t->text('deskripsi')->nullable();
            if(!Schema::hasColumn('produk','gambar')) $t->string('gambar')->nullable();
            if(!Schema::hasColumn('produk','status')) $t->boolean('status')->default(true);
        });
        Schema::table('kategori', function (Blueprint $t) {
            if(!Schema::hasColumn('kategori','gambar')) $t->string('gambar')->nullable();
        });
    }
    public function down(){
        Schema::table('produk', fn(Blueprint $t)=>$t->dropColumn(['sku','satuan','deskripsi','gambar','status']));
        Schema::table('kategori', fn(Blueprint $t)=>$t->dropColumn('gambar'));
    }
};