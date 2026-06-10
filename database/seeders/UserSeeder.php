<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@pesantrenmu.id',
                'role' => 'admin',
            ],
            [
                'name' => 'Asesor Ketua',
                'email' => 'asesor_ketua@pesantrenmu.id',
                'role' => 'asesor',
            ],
            [
                'name' => 'Asesor Anggota',
                'email' => 'asesor_anggota@pesantrenmu.id',
                'role' => 'asesor',
            ],
            [
                'name' => 'Pesantren',
                'email' => 'pesantren@pesantrenmu.id',
                'role' => 'pesantren',
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@pesantrenmu.id',
                'role' => 'super_admin',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('parameter', $userData['role'])->first();

            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'role_id' => $role?->id,
                    'uuid' => Str::uuid(),
                    'status' => 'active',
                ]
            );
        }
    }
}
