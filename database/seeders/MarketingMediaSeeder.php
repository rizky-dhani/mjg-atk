<?php

namespace Database\Seeders;

use App\Models\MarketingMedia;
use Illuminate\Database\Seeder;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaCategory;

class MarketingMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = MarketingMediaCategory::all();

        if ($categories->isEmpty()) {
            return;
        }

        foreach ($categories as $category) {
            MarketingMediaItem::create([
                'name' => $category->name . ' Media',
                'slug' => $category->slug . '-media',
                'category_id' => $category->id,
                'size' => 'A4',
                'unit_of_measure' => 'sheet',
            ]);
        }
    }
}
