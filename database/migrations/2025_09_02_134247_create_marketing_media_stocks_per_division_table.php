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
        Schema::create('mm_stocks_per_division', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketing_media_categories')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['division_id', 'item_id']);
            $table->index(['division_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mm_stocks_per_division');
    }
};

