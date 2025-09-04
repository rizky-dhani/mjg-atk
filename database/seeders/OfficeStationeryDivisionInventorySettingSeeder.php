<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyDivision;
use App\Models\OfficeStationeryItem;
use App\Models\OfficeStationeryDivisionInventorySetting;

class DivisionInventorySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = CompanyDivision::all();
        $items = OfficeStationeryItem::all();

        foreach ($divisions as $division) {
            foreach ($items as $item) {
                OfficeStationeryDivisionInventorySetting::updateOrCreate(
                    [
                        'division_id' => $division->id,
                        'item_id' => $item->id,
                        'category_id' => $item->category->id,
                    ],
                    [
                        'max_limit' => 50,
                    ]
                );
            }
        }
    }
}