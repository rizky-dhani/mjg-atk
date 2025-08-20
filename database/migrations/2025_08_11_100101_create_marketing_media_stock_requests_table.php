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
        Schema::create('marketing_media_stock_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_media_id')->constrained('marketing_media')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('company_divisions')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('type', ['increase']);
            $table->enum('status', ['pending', 'approved_by_head', 'rejected_by_head', 'approved_by_ga_admin', 'rejected_by_ga_admin', 'approved_by_mkt_head', 'rejected_by_mkt_head', 'completed'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approval_head_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('approval_head_at')->nullable();
            $table->foreignId('rejection_head_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('rejection_head_at')->nullable();
            $table->foreignId('approval_admin_ga_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('approval_admin_ga_at')->nullable();
            $table->foreignId('rejection_admin_ga_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('rejection_admin_ga_at')->nullable();
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
