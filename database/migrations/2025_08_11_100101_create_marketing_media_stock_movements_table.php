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
        Schema::create('marketing_media_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_media_id')->constrained('marketing_media')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment', 'damaged', 'expired']);
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->text('notes')->nullable();
            $table->date('movement_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_media_stock_movements');
    }
};
