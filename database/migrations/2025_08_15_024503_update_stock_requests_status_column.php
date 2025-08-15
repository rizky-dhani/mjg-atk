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
                'approved_by_ga_admin',
                'rejected_by_ga_admin',
                'approved_by_ga_head',
                'rejected_by_ga_head',
                'completed'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved_by_head',
                'rejected_by_head',
                'approved_by_ipc',
                'rejected_by_ipc',
                'approved_by_ipc_head',
                'delivered',
                'approved_stock_adjustment',
                'approved_by_ga_admin',
                'approved_by_ga_head',
                'completed'
            ])->change();
        });
    }
};
