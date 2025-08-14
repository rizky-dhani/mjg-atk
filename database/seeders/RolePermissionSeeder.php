<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles and assign permissions
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Head']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Staff']);
    }
}
