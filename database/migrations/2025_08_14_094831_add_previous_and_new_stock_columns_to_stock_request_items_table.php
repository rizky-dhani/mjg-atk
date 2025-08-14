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
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->integer('previous_stock')->nullable()->after('quantity');
            $table->integer('new_stock')->nullable()->after('previous_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->dropColumn(['previous_stock', 'new_stock']);
        });
    }
};
