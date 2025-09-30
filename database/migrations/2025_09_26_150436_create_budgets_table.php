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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('company_divisions')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('initial_amount');
            $table->integer('current_amount')->nullable();
            $table->year('effective_year');
            $table->enum('type', ['ATK', 'Marketing Media']); // Budget type: ATK or Marketing Media
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure unique combination of division and type
            $table->unique(['division_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
