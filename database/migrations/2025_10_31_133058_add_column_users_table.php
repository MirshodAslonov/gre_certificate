<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // telegram_id ustunini string ga o'zgartirish
            $table->string('telegram_id', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // avvalgi BIGINT ga qaytarish (masalan)
            $table->bigInteger('telegram_id')->change();
        });
    }
};
