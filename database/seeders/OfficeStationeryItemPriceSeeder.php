<?php

namespace Database\Seeders;

use App\Models\OfficeStationeryItemPrice;
use App\Models\OfficeStationeryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeStationeryItemPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all office stationery items
        $items = OfficeStationeryItem::all();
        
        $priceSteps = [1000, 2000, 3000, 4000, 5000];
        $stepIndex = 0;
        
        foreach ($items as $item) {
            // Create a default price for each item with price from 1000 to 5000
            OfficeStationeryItemPrice::create([
                'item_id' => $item->id,
                'price' => $priceSteps[$stepIndex % count($priceSteps)], // Cycle through price steps
                'effective_date' => now(),
                'notes' => 'Default price for ' . $item->name,
            ]);
            
            $stepIndex++;
        }
    }
}