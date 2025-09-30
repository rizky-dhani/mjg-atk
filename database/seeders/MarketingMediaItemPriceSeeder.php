<?php

namespace Database\Seeders;

use App\Models\MarketingMediaItemPrice;
use App\Models\MarketingMediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingMediaItemPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all marketing media items
        $items = MarketingMediaItem::all();
        
        $priceSteps = [15000, 25000, 35000, 45000, 55000];
        $stepIndex = 0;
        
        foreach ($items as $item) {
            // Create a default price for each item with price from 15000 to 55000
            MarketingMediaItemPrice::create([
                'item_id' => $item->id,
                'price' => $priceSteps[$stepIndex % count($priceSteps)], // Cycle through price steps
                'effective_date' => now(),
                'notes' => 'Default price for ' . $item->name,
            ]);
            
            $stepIndex++;
        }
    }
}