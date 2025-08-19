<?php

namespace Database\Seeders;

use App\Models\CompanyDivision;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompanyDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyDivision::insert([
            ['name' => 'Accounting', 'initial' => 'ACC'],
            ['name' => 'Business Development', 'initial' => 'BDV'],
            ['name' => 'Finance', 'initial' => 'FIN'],
            ['name' => 'Human Capital General', 'initial' => 'HCG'],
            ['name' => 'Import and Purchasing', 'initial' => 'IPC'],
            ['name' => 'Information Technology', 'initial' => 'ITD'],
            ['name' => 'Legal and Compliance', 'initial' => 'LAC'],
            ['name' => 'Medical Affairs', 'initial' => 'MED'],
            ['name' => 'Marketing Blood Bank', 'initial' => 'MBB'],
            ['name' => 'Marketing Hospital', 'initial' => 'MHO'],
            ['name' => 'Marketing Primary Care', 'initial' => 'MPC'],
            ['name' => 'Marketing Primary Health', 'initial' => 'MPH'],
            ['name' => 'Marketing Support', 'initial' => 'MKS'],
            ['name' => 'Regulatory Affairs', 'initial' => 'RA'],
            ['name' => 'Quality Assurance', 'initial' => 'QA'],
            ['name' => 'Revenue Funnel', 'initial' => 'REV'],
        ]);
    }
}
