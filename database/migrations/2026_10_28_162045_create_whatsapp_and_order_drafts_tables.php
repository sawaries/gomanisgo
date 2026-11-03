<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat dan deduplikasi pesan WhatsApp
        Schema::create('whatsapp_messages', function (Blueprint $t) {
            $t->id();
            $t->string('whatsapp_message_id', 100)->unique();
            $t->string('sender_phone', 30)->index();
            $t->string('message_type', 30)->default('text');
            $t->text('message_content')->nullable();
            $t->string('processing_status', 30)->default('received');
            $t->timestamp('received_at')->useCurrent();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        // Draft pesanan untuk percakapan WhatsApp
        Schema::create('order_drafts', function (Blueprint $t) {
            $t->id();
            $t->string('sender_phone', 30)->index();
            $t->string('sender_name', 100)->nullable();
            $t->json('items');
            $t->decimal('total', 15, 2)->default(0);
            $t->string('status', 30)->default('menunggu_konfirmasi');
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_drafts');
        Schema::dropIfExists('whatsapp_messages');
    }
};
