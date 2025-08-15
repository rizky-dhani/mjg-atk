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
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('rejection_head_id')->nullable()->after('approval_head_at');
            $table->timestamp('rejection_head_at')->nullable()->after('rejection_head_id');
            $table->unsignedBigInteger('rejection_ipc_id')->nullable()->after('approval_ipc_at');
            $table->timestamp('rejection_ipc_at')->nullable()->after('rejection_ipc_id');
            $table->unsignedBigInteger('rejection_ipc_head_id')->nullable()->after('approval_ipc_head_at');
            $table->timestamp('rejection_ipc_head_at')->nullable()->after('rejection_ipc_head_id');
            $table->unsignedBigInteger('rejection_stock_adjustment_id')->nullable()->after('approval_stock_adjustment_at');
            $table->timestamp('rejection_stock_adjustment_at')->nullable()->after('rejection_stock_adjustment_id');
            $table->unsignedBigInteger('rejection_ga_admin_id')->nullable()->after('approval_ga_admin_at');
            $table->timestamp('rejection_ga_admin_at')->nullable()->after('rejection_ga_admin_id');
            $table->unsignedBigInteger('rejection_ga_head_id')->nullable()->after('approval_ga_head_at');
            $table->timestamp('rejection_ga_head_at')->nullable()->after('rejection_ga_head_id');
            
            $table->foreign('rejection_head_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejection_ipc_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejection_ipc_head_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejection_stock_adjustment_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejection_ga_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejection_ga_head_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropForeign(['rejection_head_id']);
            $table->dropForeign(['rejection_ipc_id']);
            $table->dropForeign(['rejection_ipc_head_id']);
            $table->dropForeign(['rejection_stock_adjustment_id']);
            $table->dropForeign(['rejection_ga_admin_id']);
            $table->dropForeign(['rejection_ga_head_id']);
            
            $table->dropColumn([
                'rejection_head_id',
                'rejection_head_at',
                'rejection_ipc_id',
                'rejection_ipc_at',
                'rejection_ipc_head_id',
                'rejection_ipc_head_at',
                'rejection_stock_adjustment_id',
                'rejection_stock_adjustment_at',
                'rejection_ga_admin_id',
                'rejection_ga_admin_at',
                'rejection_ga_head_id',
                'rejection_ga_head_at',
            ]);
        });
    }
};
