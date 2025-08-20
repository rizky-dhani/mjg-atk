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
        Schema::create('marketing_media_stock_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_request_id')->constrained('marketing_media_stock_requests')->onDelete('cascade');
            $table->foreignId('marketing_media_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketing_media_categories')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('adjusted_quantity')->nullable();
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
        Schema::dropIfExists('mm_stock_request_items');
    }
};