<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['parameter' => 'admin'],
            ['name' => 'Admin']
        );

        Role::firstOrCreate(
            ['parameter' => 'asesor'],
            ['name' => 'Asesor']
        );

        Role::firstOrCreate(
            ['parameter' => 'pesantren'],
            ['name' => 'Pesantren']
        );

        Role::firstOrCreate(
            ['parameter' => 'super_admin'],
            ['name' => 'SuperAdmin']
        );
    }
}
