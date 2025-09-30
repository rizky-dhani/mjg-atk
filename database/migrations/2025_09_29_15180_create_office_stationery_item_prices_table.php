<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_stationery_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable()->references('id')->on('office_stationery_items')->nullOnDelete();
            $table->integer('price'); // Price per unit
            $table->date('effective_date')->default(now()); // When this price becomes effective
            $table->date('end_date')->nullable(); // When this price ends (null means still active)
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for efficient lookups
            $table->index(['item_id']);
            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_stationery_item_prices');
    }
};