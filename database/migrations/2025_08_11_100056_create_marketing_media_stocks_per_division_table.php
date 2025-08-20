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
            $table->foreignId('marketing_media_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->timestamps();
            
            // Ensure we don't have duplicate entries for the same media and division
            $table->unique(['marketing_media_id', 'division_id']);
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