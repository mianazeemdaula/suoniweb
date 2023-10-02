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
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id'); // The user who is blocking
            $table->unsignedBigInteger('blocked_user_id'); // The user who is blocked
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('blocked_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};
