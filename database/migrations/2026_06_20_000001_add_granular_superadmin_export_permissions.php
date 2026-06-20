<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'Export Dashboard Super Admin', 'key' => 'superadmin.dashboard.export'],
        ['name' => 'Export Workload Asesor Super Admin', 'key' => 'superadmin.assessor_workload.export'],
        ['name' => 'Export Akreditasi Super Admin', 'key' => 'superadmin.akreditasi.export'],
        ['name' => 'Export Nilai Akreditasi Super Admin', 'key' => 'superadmin.akreditasi_scores.export'],
        ['name' => 'Export Status Dokumen Akreditasi Super Admin', 'key' => 'superadmin.akreditasi_documents.export'],
        ['name' => 'Export SK Super Admin', 'key' => 'superadmin.sk.export'],
        ['name' => 'Export Role Super Admin', 'key' => 'superadmin.roles.export'],
        ['name' => 'Export User Super Admin', 'key' => 'superadmin.users.export'],
        ['name' => 'Export Audit Super Admin', 'key' => 'superadmin.audit.export'],
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
            ->orWhereIn('id', [4])
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
