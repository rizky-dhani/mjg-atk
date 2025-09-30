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
            MarketingMediaCategorySeeder::class,
            MarketingMediaItemSeeder::class,
            OfficeStationeryDivisionInventorySettingSeeder::class,
            MarketingMediaDivisionInventorySettingSeeder::class,
            OfficeStationeryStockPerDivisionSeeder::class,
            MarketingMediaStockPerDivisionSeeder::class,
            OfficeStationeryItemPriceSeeder::class,
            MarketingMediaItemPriceSeeder::class,
            OfficeStationeryStockRequestSeeder::class,
            MarketingMediaStockRequestSeeder::class,
            OfficeStationeryStockUsageSeeder::class,
            MarketingMediaStockUsageSeeder::class,
            MarketingMediaItemPriceSeeder::class,
            OfficeStationeryItemPriceSeeder::class
        ]);

    }
}
