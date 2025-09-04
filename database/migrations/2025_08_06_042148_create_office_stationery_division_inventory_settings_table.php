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
        Schema::create('os_division_inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('office_stationery_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('office_stationery_categories')->onDelete('cascade');
            $table->integer('max_limit')->default(0);
            $table->timestamps();
            
            // Add unique constraint to prevent duplicates
            $table->unique(['division_id', 'item_id']);
            
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
        Schema::dropIfExists('os_division_inventory_settings');
    }
};
