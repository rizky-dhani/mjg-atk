<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyDivision;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaDivisionInventorySetting;

class MarketingMediaDivisionInventorySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all Marketing divisions
        $marketingDivisions = CompanyDivision::where('name', 'like', '%Marketing%')->get();
        $items = MarketingMediaItem::all();

        foreach ($marketingDivisions as $division) {
            foreach ($items as $item) {
                MarketingMediaDivisionInventorySetting::updateOrCreate(
                    [
                        'division_id' => $division->id,
                        'item_id' => $item->id,
                        'category_id' => $item->category->id,
                    ],
                    [
                        'max_limit' => 100, // Default max limit for Marketing Media items
                    ]
                );
            }
        }
    }
}