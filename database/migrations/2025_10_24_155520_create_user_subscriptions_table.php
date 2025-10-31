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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at')->comment('Obuna boshlanish vaqti');
            $table->dateTime('expires_at')->comment('Tugash vaqti');
            $table->decimal('total_amount', 16, 2)->default(0)->comment('To‘lov summasi (qancha bo‘lishi kerak)');
            $table->decimal('paid_amount', 16, 2)->default(0)->comment('To‘lov qilindi (qancha to‘landi)');
            $table->date('partial_payment_requested_at')->nullable()->comment('Qism to‘lov so‘ralgan sana');
            $table->boolean('is_active')->default(true)->comment('Obuna faol yoki yo‘qligi');
            $table->text('invite_link')->nullable()->comment('Guruh uchun havola');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
