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
        Schema::create('mm_stock_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique();
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('decrease'); // decrease
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Approval workflow fields
            $table->foreignId('approval_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approval_head_at')->nullable();
            $table->foreignId('rejection_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejection_head_at')->nullable();
            $table->foreignId('approval_ga_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approval_ga_admin_at')->nullable();
            $table->foreignId('rejection_ga_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejection_ga_admin_at')->nullable();
            $table->foreignId('approval_ga_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approval_marketing_head_at')->nullable();
            $table->foreignId('rejection_marketing_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejection_marketing_head_at')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['division_id', 'status']);
            $table->index(['requested_by', 'created_at']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mm_stock_usages');
    }
};
