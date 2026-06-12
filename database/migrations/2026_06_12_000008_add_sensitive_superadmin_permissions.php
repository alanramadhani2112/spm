<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'Update Super Admin Settings', 'key' => 'settings.update'],
        ['name' => 'Update Role Permissions', 'key' => 'role.permissions.update'],
        ['name' => 'Update User Access', 'key' => 'user.access.update'],
        ['name' => 'Approve Final Akreditasi', 'key' => 'akreditasi.final.approve'],
        ['name' => 'Publish SK Akreditasi', 'key' => 'sk.publish'],
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
