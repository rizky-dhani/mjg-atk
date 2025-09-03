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
        Schema::table('mm_stock_request_items', function (Blueprint $table) {
            $table->foreignId('stock_request_id')->constrained('marketing_media_stock_requests')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketing_media_categories')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('adjusted_quantity')->nullable();
            $table->integer('previous_stock')->nullable();
            $table->integer('new_stock')->nullable();
            $table->text('notes')->nullable();
            
            // Indexes for better query performance
            $table->index(['stock_request_id', 'item_id']);
            $table->index(['item_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mm_stock_request_items', function (Blueprint $table) {
            $table->dropForeign(['stock_request_id']);
            $table->dropForeign(['item_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['stock_request_id', 'item_id', 'category_id', 'quantity', 'adjusted_quantity', 'previous_stock', 'new_stock', 'notes']);
            $table->dropIndex(['stock_request_id', 'item_id']);
            $table->dropIndex(['item_id', 'category_id']);
        });
    }
};
