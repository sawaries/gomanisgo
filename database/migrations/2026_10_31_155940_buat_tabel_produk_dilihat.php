<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(){
        if(!Schema::hasTable('produk_dilihat')){
            Schema::create('produk_dilihat', function (Blueprint $t) {
                $t->integer('id_user');
                $t->integer('id_produk');
                $t->timestamp('dilihat_pada')->useCurrent();
                $t->primary(['id_user','id_produk']);
            });
        }
    }
    public function down(){
        Schema::dropIfExists('produk_dilihat');
    }
};