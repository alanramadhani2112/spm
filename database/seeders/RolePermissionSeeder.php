<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissionIds = Permission::pluck('id')->toArray();

        $now = now();
        $pivots = [];

        foreach ($allPermissionIds as $pid) {
            $pivots[] = ['role_id' => 1, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }

        foreach ($allPermissionIds as $pid) {
            $pivots[] = ['role_id' => 4, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }

        $asesorKeys = Permission::where('key', 'like', 'asesor.%')
            ->orWhere('key', 'akreditasi.view_own')
            ->pluck('id')
            ->toArray();

        foreach ($asesorKeys as $pid) {
            $pivots[] = ['role_id' => 2, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }

        $pesantrenKeys = Permission::where('key', 'akreditasi.view_own')
            ->orWhere('key', 'like', 'akreditasi.submit_%')
            ->orWhere('key', 'akreditasi.upload_kartu_kendali')
            ->orWhere('key', 'akreditasi.view_hasil')
            ->orWhere('key', 'akreditasi.submit_banding')
            ->pluck('id')
            ->toArray();

        foreach ($pesantrenKeys as $pid) {
            $pivots[] = ['role_id' => 3, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('role_permission')->insertOrIgnore($pivots);
    }
}
