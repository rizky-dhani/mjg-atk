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
            $table->unsignedBigInteger('approval_ipc_head_id')->nullable()->after('approval_ipc_at');
            $table->timestamp('approval_ipc_head_at')->nullable()->after('approval_ipc_head_id');
            $table->unsignedBigInteger('approval_stock_adjustment_id')->nullable()->after('delivered_at');
            $table->timestamp('approval_stock_adjustment_at')->nullable()->after('approval_stock_adjustment_id');
            $table->unsignedBigInteger('approval_ga_admin_id')->nullable()->after('approval_stock_adjustment_at');
            $table->timestamp('approval_ga_admin_at')->nullable()->after('approval_ga_admin_id');
            $table->unsignedBigInteger('approval_ga_head_id')->nullable()->after('approval_ga_admin_at');
            $table->timestamp('approval_ga_head_at')->nullable()->after('approval_ga_head_id');
            
            $table->foreign('approval_ipc_head_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approval_stock_adjustment_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approval_ga_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approval_ga_head_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropForeign(['approval_ipc_head_id']);
            $table->dropForeign(['approval_stock_adjustment_id']);
            $table->dropForeign(['approval_ga_admin_id']);
            $table->dropForeign(['approval_ga_head_id']);
            
            $table->dropColumn([
                'approval_ipc_head_id',
                'approval_ipc_head_at',
                'approval_stock_adjustment_id',
                'approval_stock_adjustment_at',
                'approval_ga_admin_id',
                'approval_ga_admin_at',
                'approval_ga_head_id',
                'approval_ga_head_at',
            ]);
        });
    }
};
