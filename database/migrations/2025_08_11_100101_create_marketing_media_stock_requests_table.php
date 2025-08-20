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
        Schema::create('mm_stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('marketing_media_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('type', ['increase']);
            $table->enum('status', [
                'pending',
                'approved_by_head',
                'rejected_by_head',
                'approved_by_ipc',
                'rejected_by_ipc',
                'approved_by_ipc_head',
                'rejected_by_ipc_head',
                'delivered',
                'approved_stock_adjustment',
                'approved_by_second_ipc_head',
                'rejected_by_second_ipc_head',
                'approved_by_ga_admin',
                'rejected_by_ga_admin', 
                'approved_by_mkt_head', 
                'rejected_by_mkt_head', 
                'completed'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approval_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_head_at')->nullable();
            $table->foreignId('rejection_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_head_at')->nullable();
            $table->foreignId('approval_ipc_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_ipc_at')->nullable();
            $table->foreignId('rejection_ipc_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_ipc_at')->nullable();
            $table->foreignId('approval_ipc_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_ipc_head_at')->nullable();
            $table->foreignId('rejection_ipc_head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_ipc_head_at')->nullable();
            $table->foreignId('approval_stock_adjustment_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_stock_adjustment_at')->nullable();
            $table->foreignId('rejection_stock_adjustment_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_stock_adjustment_at')->nullable();
            $table->foreignId('approval_ga_admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approval_ga_admin_at')->nullable();
            $table->foreignId('rejection_ga_admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('rejection_ga_admin_at')->nullable();
            $table->foreignId('approval_mkt_head_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('approval_mkt_head_at')->nullable();
            $table->foreignId('rejection_mkt_head_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('rejection_mkt_head_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_media_stock_requests');
    }
};
