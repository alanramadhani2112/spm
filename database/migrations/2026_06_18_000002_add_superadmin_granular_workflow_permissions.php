<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'Review Tahap 2 Akreditasi', 'key' => 'akreditasi.stage2_review'],
        ['name' => 'Kelola Visitasi Akreditasi', 'key' => 'akreditasi.visitasi.manage'],
        ['name' => 'Kelola Scoring Akreditasi', 'key' => 'akreditasi.scoring.manage'],
        ['name' => 'Kelola Laporan Visitasi', 'key' => 'akreditasi.laporan.manage'],
        ['name' => 'Tolak Final Akreditasi', 'key' => 'akreditasi.final.reject'],
        ['name' => 'Upload Dokumen Akreditasi', 'key' => 'akreditasi.document.upload'],
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
            ->whereIn('parameter', ['super_admin', 'superadmin'])
            ->orWhere('id', 4)
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
