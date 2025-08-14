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
        Schema::create('print_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->nullable()->constrained('company_divisions')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('print_media_categories')->onDelete('cascade');
            $table->string('size'); // A4, A3, banner, etc.
            $table->string('unit_of_measure'); // sheet, roll, meter, etc.
            $table->integer('current_stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_media');
    }
};
