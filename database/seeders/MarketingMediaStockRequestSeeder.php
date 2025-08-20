<?php

namespace Database\Seeders;

use App\Models\MarketingMedia;
use App\Models\MarketingMediaItem;
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
                $marketingMedia = MarketingMediaItem::create([
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
                    'type' => 'increase',
                    'quantity' => rand(100, 500),
                    'created_by' => $divisionUsers->random()->id,
                    'status' => MarketingMediaStockRequest::STATUS_COMPLETED,
                    'created_at' => now('Asia/Jakarta')->addWeek()
                ]);

                // Some stock reduction movements with different statuses for testing approval workflow
                $reductionRequest = MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'type' => 'reduction',
                    'quantity' => rand(10, 50),
                    'created_by' => $divisionUsers->random()->id,
                    'status' => MarketingMediaStockRequest::STATUS_PENDING,
                    'created_at' => now('Asia/Jakarta')->addWeeks(2)
                ]);
                
                // Another reduction request that's approved by head
                $approvedByHeadRequest = MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'type' => 'reduction',
                    'quantity' => rand(10, 30),
                    'created_by' => $divisionUsers->random()->id,
                    'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                    'approval_head_id' => $divisionUsers->random()->id,
                    'approval_head_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(1),
                    'created_at' => now('Asia/Jakarta')->addWeeks(2)
                ]);
                
                // Another reduction request that's approved by GA admin
                $approvedByGaAdminRequest = MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'type' => 'reduction',
                    'quantity' => rand(10, 30),
                    'created_by' => $divisionUsers->random()->id,
                    'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    'approval_head_id' => $divisionUsers->random()->id,
                    'approval_head_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(1),
                    'approval_admin_ga_id' => $divisionUsers->random()->id,
                    'approval_admin_ga_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(2),
                    'created_at' => now('Asia/Jakarta')->addWeeks(2)
                ]);
                
                // Another reduction request that's fully approved and completed
                $completedRequest = MarketingMediaStockRequest::create([
                    'marketing_media_id' => $marketingMedia->id,
                    'type' => 'reduction',
                    'quantity' => rand(5, 20),
                    'created_by' => $divisionUsers->random()->id,
                    'status' => MarketingMediaStockRequest::STATUS_COMPLETED,
                    'approval_head_id' => $divisionUsers->random()->id,
                    'approval_head_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(1),
                    'approval_admin_ga_id' => $divisionUsers->random()->id,
                    'approval_admin_ga_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(2),
                    'approval_mkt_head_id' => $divisionUsers->random()->id,
                    'approval_mkt_head_at' => now('Asia/Jakarta')->addWeeks(2)->addDays(3),
                    'created_at' => now('Asia/Jakarta')->addWeeks(2)
                ]);
            }
        }
    }
}
