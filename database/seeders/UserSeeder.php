<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanyDivision;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // user with Super Admin role
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@medquest.co.id',
            'initial' => 'SA',
            'password' => Hash::make('Superadmin2025!'),
        ]);
        $superadmin->assignRole('Super Admin');

        $division = CompanyDivision::all();
        foreach($division as $div){
            $head = User::create([
                'name' => $div->name.' Head',
                'email' => strtolower($div->initial).'.head@medquest.co.id',
                'initial' => 'H'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id
            ]);
            $head->assignRole('Head');

            $admin = User::create([
                'name' => $div->name.' Admin',
                'email' => strtolower($div->initial).'.admin@medquest.co.id',
                'initial' => 'A'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id
            ]);
            $admin->assignRole('Admin');
        }
    }
}
