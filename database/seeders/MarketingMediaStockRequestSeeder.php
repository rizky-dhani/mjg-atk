<?php

namespace Database\Seeders;

use App\Models\MarketingMedia;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaCategory;
use App\Models\CompanyDivision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MarketingMediaStockRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = CompanyDivision::all();
        $categories = MarketingMediaCategory::all();

        if ($divisions->isEmpty() || $categories->isEmpty()) {
            return;
        }

        // Create unique MarketingMedia for each division
        foreach ($divisions as $division) {
            // Get users belonging to this division
            $divisionUsers = User::where('division_id', $division->id)->get();
            
            // Skip if no users found for this division
            if ($divisionUsers->isEmpty()) {
                continue;
            }
            
            // Create unique MarketingMedia items for this division
            $divisionMarketingMedia = [];
            foreach ($categories->take(3) as $category) {
                $marketingMedia = MarketingMedia::create([
                    'name' => $category->name . ' - ' . $division->name,
                    'category_id' => $category->id,
                    'division_id' => $division->id, // Set the division_id
                    'size' => 'A4',
                    'unit_of_measure' => 'sheet',
                ]);
                
                $divisionMarketingMedia[] = $marketingMedia;
            }
            
            // Create stock movements for this division's MarketingMedia
            foreach ($divisionMarketingMedia as $marketingMedia) {
                // Initial stock in
                MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'movement_type' => 'in',
                    'quantity' => rand(100, 500),
                    'movement_date' => now()->addWeeks(1),
                    'created_by' => $divisionUsers->random()->id,
                    'created_at' => now('Asia/Jakarta')->addWeek()
                ]);

                // Some stock out movements
                MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'movement_type' => 'out',
                    'quantity' => rand(-10, -50),
                    'movement_date' => now()->addWeeks(2),
                    'created_by' => $divisionUsers->random()->id,
                    'created_at' => now('Asia/Jakarta')->addWeeks(2)
                ]);
                
                // An adjustment movement (could be positive or negative)
                MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'movement_type' => 'adjustment',
                    'quantity' => rand(-20, 20), // Could be negative or positive
                    'movement_date' => now()->addWeeks(3),
                    'created_by' => $divisionUsers->random()->id,
                    'created_at' => now('Asia/Jakarta')->addWeeks(3)
                ]);
                
                // Recalculate stock for this marketing media
                $marketingMedia->recalculateStock();
            }
        }
    }
}
