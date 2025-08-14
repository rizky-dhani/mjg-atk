<?php

namespace Database\Seeders;

use App\Models\OfficeStationeryItem;
use App\Models\OfficeStationeryCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeStationeryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $categories = OfficeStationeryCategory::pluck('id', 'name')->toArray();

        $items = [
            // Kertas Category
            [
                'name' => 'Kertas A4 80Gr',
                'slug' => 'kertas-a4-80gr',
                'unit_of_measure' => 'rim',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas A4 70Gr',
                'slug' => 'kertas-a4-70gr',
                'unit_of_measure' => 'rim',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas F4 80Gr',
                'slug' => 'kertas-f4-80gr',
                'unit_of_measure' => 'rim',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas A4 100Gr',
                'slug' => 'kertas-a4-100gr',
                'unit_of_measure' => 'rim',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas Continuous Form 3 Ply',
                'slug' => 'kertas-continuous-form-3-ply',
                'unit_of_measure' => 'box',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas Continuous Form 4 Ply',
                'slug' => 'kertas-continuous-form-4-ply',
                'unit_of_measure' => 'box',
                'category_id' => $categories['Kertas'] ?? 1,
            ],
            [
                'name' => 'Kertas Continuous Form 6 Ply',
                'slug' => 'kertas-continuous-form-6-ply',
                'unit_of_measure' => 'box',
                'category_id' => $categories['Kertas'] ?? 1,
            ],

            // Odner Category
            [
                'name' => 'Odner Bindex F4 (isi 12 pcs)',
                'slug' => 'odner-bindex-f4',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Odner'] ?? 2,
            ],
            [
                'name' => 'Odner bantex A4 (isi 12 pcs)',
                'slug' => 'odner-bantex-a4',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Odner'] ?? 2,
            ],
            [
                'name' => 'Odner Bindex Kecil (isi 12 pcs)',
                'slug' => 'odner-bindex-kecil',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Odner'] ?? 2,
            ],

            // Buku dan Kwitansi Category
            [
                'name' => 'Buku data Kwitansi',
                'slug' => 'buku-data-kwitansi',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku tulis sekolah (38 lembar)',
                'slug' => 'buku-tulis-sekolah-38-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku tulis sekolah (58 lembar)',
                'slug' => 'buku-tulis-sekolah-58-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku Tulis Ukuran Folio',
                'slug' => 'buku-tulis-ukuran-folio',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku hard cover 200 Lembar',
                'slug' => 'buku-hard-cover-200-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku hard cover 100 Lembar',
                'slug' => 'buku-hard-cover-100-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku Akuntansi Folio 100 lembar',
                'slug' => 'buku-akuntansi-folio-100-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku Akuntansi Folio 200 lembar',
                'slug' => 'buku-akuntansi-folio-200-lembar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Buku Ekspedisi',
                'slug' => 'buku-ekspedisi',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Kwitansi Kecil',
                'slug' => 'kwitansi-kecil',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],
            [
                'name' => 'Kwitansi Besar',
                'slug' => 'kwitansi-besar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Buku dan Kwitansi'] ?? 3,
            ],

            // Pulpen, Pensil, Stabilo Category
            [
                'name' => 'Pulpen Snowman Biru (isi 12 pcs)',
                'slug' => 'pulpen-snowman-biru',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Pulpen Faster Biru C6 (12pcs)',
                'slug' => 'pulpen-faster-biru-c6',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'pulpen boxy biru',
                'slug' => 'pulpen-boxy-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Pensil 2B Steadler (12 pcs)',
                'slug' => 'pensil-2b-steadler',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Pensil Mekanik',
                'slug' => 'pensil-mekanik',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Isi Pensil Mekanik joyko',
                'slug' => 'isi-pensil-mekanik-joyko',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Stabilo Kuning',
                'slug' => 'stabilo-kuning',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Stabilo Pink',
                'slug' => 'stabilo-pink',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Stabilo Hijau',
                'slug' => 'stabilo-hijau',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Stabilo Biru',
                'slug' => 'stabilo-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Whiteboard Biru',
                'slug' => 'spidol-whiteboard-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Whiteboard Merah',
                'slug' => 'spidol-whiteboard-merah',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Whiteboard Hitam',
                'slug' => 'spidol-whiteboard-hitam',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Permanent Biru',
                'slug' => 'spidol-permanent-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Permanent Merah',
                'slug' => 'spidol-permanent-merah',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol Permanent Hitam',
                'slug' => 'spidol-permanent-hitam',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol OPP Biru',
                'slug' => 'spidol-opp-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],
            [
                'name' => 'Spidol OPP Biru',
                'slug' => 'spidol-opp-biru-2',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Pulpen, Pensil, Stabilo'] ?? 4,
            ],

            // Binder Clip Category
            [
                'name' => 'Binder Clip No 107 (isi 12 pcs)',
                'slug' => 'binder-clip-no-107',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 111 (isi 12 pcs)',
                'slug' => 'binder-clip-no-111',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 155 (isi 12 pcs)',
                'slug' => 'binder-clip-no-155',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 260 (isi 12 pcs)',
                'slug' => 'binder-clip-no-260',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 105 (isi 12 pcs)',
                'slug' => 'binder-clip-no-105',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 200 (isi 12 pcs)',
                'slug' => 'binder-clip-no-200',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 208 (isi 6 pcs)',
                'slug' => 'binder-clip-no-208',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],
            [
                'name' => 'Binder Clip No 315 (isi 6 pcs)',
                'slug' => 'binder-clip-no-315',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Binder Clip'] ?? 5,
            ],

            // Label T&J Category
            [
                'name' => 'Label T & J No 107 (isi 10 lembar)',
                'slug' => 'label-t-j-no-107',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Label T&J'] ?? 6,
            ],
            [
                'name' => 'Label T & J No 123 (isi 10 lembar)',
                'slug' => 'label-t-j-no-123',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Label T&J'] ?? 6,
            ],
            [
                'name' => 'Label T & J No 100 (isi 10 lembar)',
                'slug' => 'label-t-j-no-100',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Label T&J'] ?? 6,
            ],
            [
                'name' => 'Label T & J No 99 (isi 10 lembar)',
                'slug' => 'label-t-j-no-99',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Label T&J'] ?? 6,
            ],

            // Lain-Lain Category
            [
                'name' => 'Amplop Putih Kecil (104)',
                'slug' => 'amplop-putih-kecil-104',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Amplop Putih Besar (90)',
                'slug' => 'amplop-putih-besar-90',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Amplop Coklat Kecil (100)',
                'slug' => 'amplop-coklat-kecil-100',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Amplop Coklat Polos A4',
                'slug' => 'amplop-coklat-polos-a4',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Amplop Coklat Polos F4',
                'slug' => 'amplop-coklat-polos-f4',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Business File (12 pcs)',
                'slug' => 'business-file',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Clear Holder',
                'slug' => 'clear-holder',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Clear Holder CD 40',
                'slug' => 'clear-holder-cd-40',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Clear Holder T & FC (isi 12pcs)',
                'slug' => 'clear-holder-t-fc',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Plastik Bantex 8843 (isi 100) 2044',
                'slug' => 'plastik-bantex-8843',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Map Diamond A4',
                'slug' => 'map-diamond-a4',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Map plastik kancing (12 pcs)',
                'slug' => 'map-plastik-kancing',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Cutter Joyko Kecil',
                'slug' => 'cutter-joyko-kecil',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Cutter Joyko Kecil',
                'slug' => 'cutter-joyko-kecil-2',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'isi Cutter Kecil',
                'slug' => 'isi-cutter-kecil',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Double Tip Liquid 2,4cm',
                'slug' => 'double-tip-liquid-2-4cm',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Lakban Coklat',
                'slug' => 'lakban-coklat',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Lakban Bening',
                'slug' => 'lakban-bening',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Isolasi 1/2 gamfix (6 roll)',
                'slug' => 'isolasi-1-2-gamfix',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Glue Stick Joyko 8g Kenko 654',
                'slug' => 'glue-stick-joyko-8g-kenko-654',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Joyko Trigonal Clip no 3 (isi 10 dus kecil)',
                'slug' => 'joyko-trigonal-clip-no-3',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Joyko Trigonal Clip no 1 (isi 10 dus kecil)',
                'slug' => 'joyko-trigonal-clip-no-1',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Penggaris Besi 30cm',
                'slug' => 'penggaris-besi-30cm',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Penggaris Plastik 30cm',
                'slug' => 'penggaris-plastik-30cm',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Penghapus Pensil Steadler besar',
                'slug' => 'penghapus-pensil-steadler-besar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post It Kuning 653',
                'slug' => 'post-it-kuning-653',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post It Kuning 655',
                'slug' => 'post-it-kuning-655',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post It Kuning 656',
                'slug' => 'post-it-kuning-656',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post It Kuning 657',
                'slug' => 'post-it-kuning-657',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post It Kuning 660',
                'slug' => 'post-it-kuning-660',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post it Warna kode 680',
                'slug' => 'post-it-warna-kode-680',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Post it Sign Here warna warni',
                'slug' => 'post-it-sign-here-warna-warni',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Raudan pensil meja',
                'slug' => 'raudan-pensil-meja',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Papan Jalan triplek',
                'slug' => 'papan-jalan-triplek',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Papan jalan plastik',
                'slug' => 'papan-jalan-plastik',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Paper Tray 3 Susun',
                'slug' => 'paper-tray-3-susun',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Staples besar',
                'slug' => 'staples-besar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Staples kecil',
                'slug' => 'staples-kecil',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'isi Staples No 10 (seeram)',
                'slug' => 'isi-staples-no-10-seeram',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'isi Staples No 3 (seeram)',
                'slug' => 'isi-staples-no-3-seeram',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'staples remover',
                'slug' => 'staples-remover',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Tinta Fax Panasonic KX-FA-93',
                'slug' => 'tinta-fax-panasonic-kx-fa-93',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Divider Bantex Huruf 6286-05 / 6203',
                'slug' => 'divider-bantex-huruf-6286-05-6203',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Plastik Laminating Folio (100 pcs)',
                'slug' => 'plastik-laminating-folio',
                'unit_of_measure' => 'dus',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Kalkulator SDC 812',
                'slug' => 'kalkulator-sdc-812',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Genting',
                'slug' => 'genting',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Tempat pulpen 3 sekat',
                'slug' => 'tempat-pulpen-3-sekat',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Plong Kertas bantex',
                'slug' => 'plong-kertas-bantex',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Bindex box file',
                'slug' => 'bindex-box-file',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Map snelhecter bantex artline',
                'slug' => 'map-snelhecter-bantex-artline',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Tinta stempel biru',
                'slug' => 'tinta-stempel-biru',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Tempat isolasi joyko TD-103',
                'slug' => 'tempat-isolasi-joyko-td-103',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Pembolong kertas kecil',
                'slug' => 'pembolong-kertas-kecil',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Pembolong kertas besar',
                'slug' => 'pembolong-kertas-besar',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Tape dispenser lakban besar TD 2',
                'slug' => 'tape-dispenser-lakban-besar-td-2',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'DVD RW',
                'slug' => 'dvd-rw',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'DVD RW casing',
                'slug' => 'dvd-rw-casing',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
            [
                'name' => 'Paper fastener',
                'slug' => 'paper-fastener',
                'unit_of_measure' => 'pcs',
                'category_id' => $categories['Lain-Lain'] ?? 7,
            ],
        ];

        OfficeStationeryItem::insert($items);
    }
}
