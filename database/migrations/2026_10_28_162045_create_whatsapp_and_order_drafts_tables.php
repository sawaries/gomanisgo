<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom sumber dan nomor_wa ke tabel transaksi jika belum ada
        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedInteger('id_keranjang')->nullable()->change();

            if (! Schema::hasColumn('transaksi', 'sumber')) {
                $table->string('sumber', 20)->default('website')->after('id_keranjang');
            }
            if (! Schema::hasColumn('transaksi', 'nomor_wa')) {
                $table->string('nomor_wa', 30)->nullable()->after('sumber');
            }
            if (! Schema::hasColumn('transaksi', 'catatan')) {
                $table->text('catatan')->nullable()->after('status');
            }
        });

        // 2. Tambah kolom snapshot nama produk ke detail_transaksi jika belum ada
        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_transaksi', 'nama_produk_snapshot')) {
                $table->string('nama_produk_snapshot', 150)->nullable()->after('id_produk');
            }
        });

        // 3. Tabel riwayat dan deduplikasi pesan WhatsApp
        if (! Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->string('whatsapp_message_id', 100)->unique();
                $table->string('sender_phone', 30)->index();
                $table->string('message_type', 30)->default('text');
                $table->text('message_content')->nullable();
                $table->string('processing_status', 30)->default('received');
                $table->timestamp('received_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tabel draft pesanan untuk percakapan WhatsApp
        if (! Schema::hasTable('order_drafts')) {
            Schema::create('order_drafts', function (Blueprint $table) {
                $table->id();
                $table->string('sender_phone', 30)->index();
                $table->string('sender_name', 100)->nullable();
                $table->json('items');
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status', 30)->default('menunggu_konfirmasi');
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_drafts');
        Schema::dropIfExists('whatsapp_messages');

        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('detail_transaksi', 'nama_produk_snapshot')) {
                $table->dropColumn('nama_produk_snapshot');
            }
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('transaksi', 'catatan')) {
                $drops[] = 'catatan';
            }
            if (Schema::hasColumn('transaksi', 'nomor_wa')) {
                $drops[] = 'nomor_wa';
            }
            if (Schema::hasColumn('transaksi', 'sumber')) {
                $drops[] = 'sumber';
            }
            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
