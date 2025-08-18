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
        Schema::create('os_stocks_per_division', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('office_stationery_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('office_stationery_categories')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_stationery_stocks_per_divisions');
    }
};
