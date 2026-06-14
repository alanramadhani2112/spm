<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_export_filtered_audit_trail(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4, 'name' => 'Super Auditor']);
        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $superAdmin->id,
            'actor_user_id' => $superAdmin->id,
            'action_type' => 'status_changed',
            'from_status' => Akreditasi::STATUS_DRAFT_PROFILE,
            'to_status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
            'reason' => 'Pengajuan awal.',
            'metadata' => ['source' => 'test'],
        ]);
        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $superAdmin->id,
            'actor_user_id' => $superAdmin->id,
            'action_type' => 'approved',
            'reason' => 'Tidak masuk filter.',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.audit.index', ['action' => 'status_changed']))
            ->assertOk()
            ->assertSee('Export CSV');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.audit.export', ['action' => 'status_changed']))
            ->assertOk()
            ->assertDownload('audit-trail-action-status-changed.csv');

        $auditLog = AkreditasiAuditLog::where('action_type', 'superadmin_exported')->firstOrFail();
        $this->assertSame($superAdmin->id, $auditLog->user_id);
        $this->assertSame('audit_trail', $auditLog->metadata['export_type']);
        $this->assertSame('csv', $auditLog->metadata['format']);
        $this->assertSame('status_changed', $auditLog->metadata['filters']['action']);
        $this->assertSame(1, $auditLog->metadata['rows_exported']);
        $this->assertSame(5000, $auditLog->metadata['row_limit']);
    }

    public function test_super_admin_without_export_permission_cannot_export_audit_trail(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $this->revokeSuperAdminPermission('superadmin.export');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.audit.export'))
            ->assertForbidden();
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}
