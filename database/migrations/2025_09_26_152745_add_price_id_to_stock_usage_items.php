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
        // Add price_id to office stationery stock usage items
        Schema::table('os_stock_usage_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price_id')->nullable()->after('notes');
            $table->foreign('price_id')->references('id')->on('item_prices')->onDelete('set null');
        });

        // Add price_id to marketing media stock usage items
        Schema::table('mm_stock_usage_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price_id')->nullable()->after('notes');
            $table->foreign('price_id')->references('id')->on('item_prices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('os_stock_usage_items', function (Blueprint $table) {
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
        });

        Schema::table('mm_stock_usage_items', function (Blueprint $table) {
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
        });
    }
};
