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
        Schema::create('mm_stock_usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_usage_id')->constrained('mm_stock_usages')->onDelete('cascade');
            $table->foreignId('marketing_media_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketing_media_categories')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('previous_stock')->nullable();
            $table->integer('new_stock')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mm_stock_usage_items');
    }
};