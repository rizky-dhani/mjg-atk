<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MarketingMedia;
use App\Models\MarketingMediaStockPerDivision;

class MarketingMediaStockPerDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all marketing media items
        $marketingMediaItems = MarketingMedia::all();
        
        foreach ($marketingMediaItems as $media) {
            // Create a stock record for each media item in its division
            MarketingMediaStockPerDivision::firstOrCreate([
                'marketing_media_id' => $media->id,
                'division_id' => $media->division_id,
            ], [
                'current_stock' => 0, // Initialize with 0 stock
            ]);
        }
    }
}
