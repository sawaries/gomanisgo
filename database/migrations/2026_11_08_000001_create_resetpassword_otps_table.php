<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resetpassword_otps', function (Blueprint $t) {
            $t->string('email', 100)->primary();
            $t->string('otp_hash');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('expires_at');
            $t->timestamp('last_sent_at');
            $t->string('reset_token_hash')->nullable();
            $t->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resetpassword_otps');
    }
};