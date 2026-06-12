<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'Review Awal Akreditasi', 'key' => 'akreditasi.review_awal'],
        ['name' => 'Review Tahap 1 Akreditasi', 'key' => 'akreditasi.stage1_review'],
        ['name' => 'Assign Asesor Akreditasi', 'key' => 'akreditasi.assign_asesor'],
        ['name' => 'Proses Banding Akreditasi', 'key' => 'akreditasi.proses_banding'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $permission['key']],
                [
                    'name' => $permission['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('key', array_column($this->permissions, 'key'))
            ->pluck('id');

        $roleIds = DB::table('roles')
            ->whereIn('parameter', ['admin', 'super_admin', 'superadmin'])
            ->orWhereIn('id', [1, 4])
            ->pluck('id')
            ->unique();

        $pivots = [];

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $pivots[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permission')->insertOrIgnore($pivots);
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('key', array_column($this->permissions, 'key'))
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
