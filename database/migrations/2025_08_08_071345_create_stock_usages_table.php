<?php

use App\Models\User;
use App\Models\CompanyDivision;
use App\Models\OfficeStationeryItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CompanyDivision::class, 'division_id')->nullable()->constrained('company_divisions')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'requester_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->foreignIdFor(User::class, 'head_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_usages');
    }
};
