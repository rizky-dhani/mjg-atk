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
        Schema::create('os_stock_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->enum('type', ['decrease']);
            $table->enum('status', [
                'pending',
                'approved_by_head',
                'rejected_by_head',
                'approved_by_ga_admin',
                'rejected_by_ga_admin',
                'approved_by_hcg_head',
                'rejected_by_hcg_head',
                'completed'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approval_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_head_at')->nullable();
            $table->foreignId('rejection_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_head_at')->nullable();
            $table->foreignId('approval_ga_admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_ga_admin_at')->nullable();
            $table->foreignId('rejection_ga_admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_ga_admin_at')->nullable();
            $table->foreignId('approval_hcg_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_hcg_head_at')->nullable();
            $table->foreignId('rejection_hcg_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_hcg_head_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_stationery_stock_usages');
    }
};
