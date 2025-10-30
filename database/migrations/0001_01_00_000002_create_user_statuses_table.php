<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_statuses', function (Blueprint $table) {
             $table->id();
            $table->string('name'); // active/inactive/blocked
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('user_statuses')->insert([
            ['name' => 'active',
            'description' => 'User is active',],
            ['name' => 'block',
            'description' => 'User is blocked',],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
