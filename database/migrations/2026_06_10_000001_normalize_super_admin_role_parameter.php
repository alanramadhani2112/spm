<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $canonicalId = DB::table('roles')->where('parameter', 'super_admin')->value('id');
        $duplicateId = DB::table('roles')->where('parameter', 'superadmin')->value('id');

        if ($canonicalId && $duplicateId) {
            $now = now();
            $duplicatePermissions = DB::table('role_permission')
                ->where('role_id', $duplicateId)
                ->pluck('permission_id')
                ->map(fn ($permissionId) => [
                    'role_id' => $canonicalId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($duplicatePermissions) {
                DB::table('role_permission')->insertOrIgnore($duplicatePermissions);
            }

            DB::table('users')->where('role_id', $duplicateId)->update(['role_id' => $canonicalId]);
            DB::table('role_permission')->where('role_id', $duplicateId)->delete();
            DB::table('roles')->where('id', $duplicateId)->delete();
        }

        if (! $canonicalId && $duplicateId) {
            DB::table('roles')
                ->where('id', $duplicateId)
                ->update(['parameter' => 'super_admin', 'name' => 'Super Admin']);
        }
    }

    public function down(): void
    {
        //
    }
};
