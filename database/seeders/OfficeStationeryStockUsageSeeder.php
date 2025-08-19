<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockUsageItem;
use App\Models\User;
use App\Models\CompanyDivision;
use App\Models\OfficeStationeryItem;

class OfficeStationeryStockUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user with Head role
        $headUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'Head');
        })->first();
        
        if (!$headUser) {
            $headUser = User::first();
        }
        
        // Get a division
        $division = CompanyDivision::first();
        
        if (!$division) {
            $division = CompanyDivision::factory()->create();
        }
        
        // Create a stock usage
        $stockUsage = OfficeStationeryStockUsage::create([
            'division_id' => $division->id,
            'requested_by' => $headUser->id,
            'status' => OfficeStationeryStockUsage::STATUS_PENDING,
            'notes' => 'Sample stock usage for testing',
        ]);
        
        // Get some office stationery items
        $items = OfficeStationeryItem::limit(2)->get();
        
        foreach ($items as $item) {
            OfficeStationeryStockUsageItem::create([
                'stock_usage_id' => $stockUsage->id,
                'item_id' => $item->id,
                'category_id' => $item->category_id,
                'quantity' => rand(1, 5),
                'notes' => 'Sample item usage',
            ]);
        }
    }
}
