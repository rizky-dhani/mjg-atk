<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the role and permission seeder first
        $this->call([
            RolePermissionSeeder::class,
            CompanyDivisionSeeder::class,
            UserSeeder::class,
            OfficeStationeryCategorySeeder::class,
            OfficeStationeryItemSeeder::class,
            DivisionInventorySettingSeeder::class,
            OfficeStationeryStockPerDivisionSeeder::class,
            StockRequestSeeder::class,
            StockAdjustmentSeeder::class,
            UpdateStockRequestRejectionFieldsSeeder::class,
            MarketingMediaCategorySeeder::class,
            MarketingMediaSeeder::class,
            MarketingMediaStockMovementSeeder::class,
        ]);

    }
}
