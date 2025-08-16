<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OfficeStationeryStockRequest;

class UpdateStockRequestStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update any existing requests with status 'rejected' to use the appropriate new status
        // Since we don't have information about which role rejected the request, we'll use a default
        OfficeStationeryStockRequest::where('status', 'rejected')->update(['status' => 'rejected_by_ipc']);
    }
}
