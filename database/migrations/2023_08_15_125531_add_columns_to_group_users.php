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
        Schema::table('group_users', function (Blueprint $table) {
            $table->float('fee')->after('allowed')->default(0);
            $table->boolean('fee_paid')->after('fee')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_users', function (Blueprint $table) {
            $table->dropColumn(['fee','fee_paid']);
        });
    }
};
