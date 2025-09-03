<?php

namespace Database\Seeders;

use App\Models\MarketingMediaCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingMediaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Printed Materials'],
            ['name' => 'Digital Assets'],
            ['name' => 'Promotional Items'],
            ['name' => 'Signage & Banners'],
            ['name' => 'Packaging Materials'],
            ['name' => 'Merchandise'],
            ['name' => 'Point of Sale'],
            ['name' => 'Corporate Identity'],
        ];

        foreach ($categories as $category) {
            MarketingMediaCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
            ]);
        }
    }
}