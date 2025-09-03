<?php

namespace Database\Seeders;

use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingMediaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $categories = MarketingMediaCategory::pluck('id', 'name')->toArray();

        $items = [
            // Printed Materials Category
            [
                'name' => 'Brochure A4 4 Pages',
                'slug' => 'brochure-a4-4-pages',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Brochure A5 4 Pages',
                'slug' => 'brochure-a5-4-pages',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Flyer A4 1 Page',
                'slug' => 'flyer-a4-1-page',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Flyer A5 1 Page',
                'slug' => 'flyer-a5-1-page',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Poster A2',
                'slug' => 'poster-a2',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Poster A1',
                'slug' => 'poster-a1',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Banner 3x1 Meter',
                'slug' => 'banner-3x1-meter',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Catalog A4 16 Pages',
                'slug' => 'catalog-a4-16-pages',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Catalog A5 16 Pages',
                'slug' => 'catalog-a5-16-pages',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],
            [
                'name' => 'Newsletter A4 8 Pages',
                'slug' => 'newsletter-a4-8-pages',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Printed Materials'] ?? 1,
            ],

            // Digital Assets Category
            [
                'name' => 'Social Media Banner 1080x1080',
                'slug' => 'social-media-banner-1080x1080',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Social Media Banner 1080x1350',
                'slug' => 'social-media-banner-1080x1350',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Social Media Banner 1080x566',
                'slug' => 'social-media-banner-1080x566',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Email Template Design',
                'slug' => 'email-template-design',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Landing Page Design',
                'slug' => 'landing-page-design',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Digital Ad Banner 300x250',
                'slug' => 'digital-ad-banner-300x250',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Digital Ad Banner 728x90',
                'slug' => 'digital-ad-banner-728x90',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Digital Ad Banner 160x600',
                'slug' => 'digital-ad-banner-160x600',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Website Banner 1920x400',
                'slug' => 'website-banner-1920x400',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],
            [
                'name' => 'Mobile App Banner 1024x500',
                'slug' => 'mobile-app-banner-1024x500',
                'unit_of_measure' => 'design',
                'category_id' => $categories['Digital Assets'] ?? 2,
            ],

            // Promotional Items Category
            [
                'name' => 'Branded Pen',
                'slug' => 'branded-pen',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'USB Flash Drive 8GB',
                'slug' => 'usb-flash-drive-8gb',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'USB Flash Drive 16GB',
                'slug' => 'usb-flash-drive-16gb',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Tote Bag',
                'slug' => 'tote-bag',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Branded Mug',
                'slug' => 'branded-mug',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Branded T-Shirt',
                'slug' => 'branded-t-shirt',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Branded Cap',
                'slug' => 'branded-cap',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Keychain',
                'slug' => 'keychain',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Notepad A5',
                'slug' => 'notepad-a5',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],
            [
                'name' => 'Sticker Pack',
                'slug' => 'sticker-pack',
                'unit_of_measure' => 'pack',
                'category_id' => $categories['Promotional Items'] ?? 3,
            ],

            // Signage & Banners Category
            [
                'name' => 'Roll Up Banner 85x200cm',
                'slug' => 'roll-up-banner-85x200cm',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'X Banner 60x160cm',
                'slug' => 'x-banner-60x160cm',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'A-Frame Sign 60x80cm',
                'slug' => 'a-frame-sign-60x80cm',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Window Sticker A4',
                'slug' => 'window-sticker-a4',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Window Sticker A3',
                'slug' => 'window-sticker-a3',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Wall Decal 50x50cm',
                'slug' => 'wall-decal-50x50cm',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Directional Sign',
                'slug' => 'directional-sign',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Safety Sign',
                'slug' => 'safety-sign',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Parking Sign',
                'slug' => 'parking-sign',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],
            [
                'name' => 'Office Sign Nameplate',
                'slug' => 'office-sign-nameplate',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Signage & Banners'] ?? 4,
            ],

            // Packaging Materials Category
            [
                'name' => 'Product Box Small',
                'slug' => 'product-box-small',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Product Box Medium',
                'slug' => 'product-box-medium',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Product Box Large',
                'slug' => 'product-box-large',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Tape Clear',
                'slug' => 'packing-tape-clear',
                'unit_of_measure' => 'roll',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Tape Brown',
                'slug' => 'packing-tape-brown',
                'unit_of_measure' => 'roll',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Bubble Wrap',
                'slug' => 'bubble-wrap',
                'unit_of_measure' => 'meter',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Peanuts',
                'slug' => 'packing-peanuts',
                'unit_of_measure' => 'liter',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Box A4',
                'slug' => 'packing-box-a4',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Box A3',
                'slug' => 'packing-box-a3',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],
            [
                'name' => 'Packing Box Letter',
                'slug' => 'packing-box-letter',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Packaging Materials'] ?? 5,
            ],

            // Merchandise Category
            [
                'name' => 'Branded Tumbler',
                'slug' => 'branded-tumbler',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Water Bottle',
                'slug' => 'branded-water-bottle',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Umbrella',
                'slug' => 'branded-umbrella',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Notebook',
                'slug' => 'branded-notebook',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Folder',
                'slug' => 'branded-folder',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Badge Holder',
                'slug' => 'branded-badge-holder',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Lanyard',
                'slug' => 'branded-lanyard',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Cooler Bag',
                'slug' => 'branded-cooler-bag',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Backpack',
                'slug' => 'branded-backpack',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],
            [
                'name' => 'Branded Phone Stand',
                'slug' => 'branded-phone-stand',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Merchandise'] ?? 6,
            ],

            // Point of Sale Category
            [
                'name' => 'Table Tent A4',
                'slug' => 'table-tent-a4',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Table Tent A5',
                'slug' => 'table-tent-a5',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Counter Display',
                'slug' => 'counter-display',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Shelf Wobbler',
                'slug' => 'shelf-wobbler',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Price Tag Holder',
                'slug' => 'price-tag-holder',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Promotional Standee',
                'slug' => 'promotional-standee',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Menu Board',
                'slug' => 'menu-board',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Dangler Sign',
                'slug' => 'dangler-sign',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Window Cling',
                'slug' => 'window-cling',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],
            [
                'name' => 'Floor Standing Poster',
                'slug' => 'floor-standing-poster',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Point of Sale'] ?? 7,
            ],

            // Corporate Identity Category
            [
                'name' => 'Business Card',
                'slug' => 'business-card',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Letterhead',
                'slug' => 'letterhead',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Envelope DL',
                'slug' => 'envelope-dl',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Envelope C4',
                'slug' => 'envelope-c4',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Envelope C5',
                'slug' => 'envelope-c5',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Company Profile Brochure',
                'slug' => 'company-profile-brochure',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Presentation Folder',
                'slug' => 'presentation-folder',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Corporate Calendar',
                'slug' => 'corporate-calendar',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Corporate Notepad',
                'slug' => 'corporate-notepad',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
            [
                'name' => 'Corporate Sticker',
                'slug' => 'corporate-sticker',
                'unit_of_measure' => 'piece',
                'category_id' => $categories['Corporate Identity'] ?? 8,
            ],
        ];

        MarketingMediaItem::insert($items);
    }
}