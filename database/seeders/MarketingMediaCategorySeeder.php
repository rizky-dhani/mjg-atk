<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\MarketingMediaCategory;

class MarketingMediaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Brochures',
                'slug' => Str::slug('Brochures'),
            ],
        ];

        foreach ($categories as $category) {
            MarketingMediaCategory::create($category);
        }
    }
}
