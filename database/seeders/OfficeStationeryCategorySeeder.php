<?php

namespace Database\Seeders;

use App\Models\OfficeStationeryCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeStationeryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OfficeStationeryCategory::insert([
            ['name' => 'Kertas'],
            ['name' => 'Odner'],
            ['name' => 'Buku dan Kwitansi'],
            ['name' => 'Pulpen, Pensil, Stabilo'],
            ['name' => 'Binder Clip'],
            ['name' => 'Label T&J'],
            ['name' => 'Lain-Lain'],
            ['name' => 'Kop Surat'],
            ['name' => 'Tambahan'],
        ]);
    }
}
