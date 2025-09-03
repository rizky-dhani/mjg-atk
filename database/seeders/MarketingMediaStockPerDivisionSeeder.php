<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketingMediaItem;
use Illuminate\Database\Seeder;
use App\Models\CompanyDivision;
use App\Models\DivisionInventorySetting;
use App\Models\MarketingMediaStockPerDivision;

class MarketingMediaStockPerDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = CompanyDivision::all();
        $items = MarketingMediaItem::all();

        foreach ($divisions as $division) {
            foreach ($items as $item) {
                $setting = DivisionInventorySetting::where('division_id', $division->id)
                    ->where('item_id', $item->id)
                    ->first();

                $maxLimit = $setting?->max_limit ?? 0;

                // Only seed if max_limit > 0
                if ($maxLimit > 0) {
                    $stock = rand(1, $maxLimit);

                    MarketingMediaStockPerDivision::updateOrCreate(
                        [
                            'division_id' => $division->id,
                            'item_id' => $item->id,
                            'category_id' => $item->category->id,
                        ],
                        [
                            'current_stock' => $stock,
                        ]
                    );
                }
            }
        }
    }
}