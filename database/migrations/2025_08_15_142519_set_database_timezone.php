<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set the database timezone to Asia/Jakarta
        DB::statement("SET time_zone = '+07:00'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset the database timezone to UTC
        DB::statement("SET time_zone = '+00:00'");
    }
};
