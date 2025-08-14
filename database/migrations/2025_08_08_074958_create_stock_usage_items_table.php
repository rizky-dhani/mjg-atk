<?php

use App\Models\StockUsage;
use App\Models\OfficeStationeryItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StockUsage::class)->nullable()->constrained('stock_usages')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('office_stationery_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('office_stationery_categories')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_usage_items');
    }
};
