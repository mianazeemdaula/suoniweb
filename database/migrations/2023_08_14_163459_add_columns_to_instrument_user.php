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
        Schema::table('instrument_user', function (Blueprint $table) {
            $table->float('fee')->default(2);
            $table->float('group_fee')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instrument_user', function (Blueprint $table) {
            $table->dropColumn(['fee','group_fee']);
        });
    }
};
