<?php

namespace Database\Seeders;

use App\Models\PrintMedia;
use App\Models\PrintMediaCategory;
use Illuminate\Database\Seeder;

class PrintMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = PrintMediaCategory::all();

        if ($categories->isEmpty()) {
            return;
        }

        foreach ($categories as $category) {
            PrintMedia::create([
                'name' => $category->name . ' Media',
                'slug' => $category->slug . '-media',
                'category_id' => $category->id,
                'size' => 'A4',
                'unit_of_measure' => 'sheet',
            ]);
        }
    }
}
