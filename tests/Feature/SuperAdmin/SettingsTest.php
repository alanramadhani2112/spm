<?php

namespace Tests\Feature\SuperAdmin;

use App\Exceptions\WorkflowException;
use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\Assessment;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SuperAdminSetting;
use App\Models\User;
use App\Services\AkreditasiWorkflowService;
use App\Services\BandingService;
use App\Services\DeadlineService;
use App\Support\SuperAdminSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->superAdmin = User::factory()->create(['role_id' => 4, 'name' => 'Super Admin', 'email' => 'super@test.com']);
        $this->admin = User::factory()->create(['role_id' => 1, 'email' => 'admin2@test.com']);
    }

    public function test_super_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertStatus(200);
    }

    public function test_super_admin_can_view_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.settings.index'))
            ->assertStatus(200);
    }

    public function test_super_admin_can_view_audit_logs(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.audit.index'))
            ->assertStatus(200);
    }

    public function test_admin_cannot_access_super_admin_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('superadmin.dashboard'))
            ->assertStatus(403);
    }

    public function test_update_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.settings.update'), [
                'key' => 'review_awal_deadline',
                'value' => '7',
                'reason' => 'testing update',
            ])
            ->assertStatus(302);
    }

    public function test_super_admin_without_settings_update_permission_cannot_update_settings(): void
    {
        $this->revokeSuperAdminPermission('settings.update');

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.settings.update'), [
                'key' => 'review_awal_deadline',
                'value' => '7',
                'reason' => 'testing update',
            ])
            ->assertForbidden();
    }

    public function test_settings_index_loads_with_nav_tabs(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.settings.deadline'))
            ->assertStatus(200);
    }

    public function test_deadline_service_uses_super_admin_deadline_keys(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::REVIEW_AWAL_DEADLINE,
            'value' => 9,
        ]);

        $this->travelTo(now()->startOfDay());

        $deadline = app(DeadlineService::class)->getDeadline('initial_review');

        $this->assertSame(now()->addDays(9)->toDateString(), $deadline?->toDateString());
    }

    public function test_stage_1_correction_limit_uses_super_admin_setting_key(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::MAX_SIKLUS_TAHAP1,
            'value' => 1,
        ]);

        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW);
        $workflow = app(AkreditasiWorkflowService::class);

        $workflow->adminStage1Review($akreditasi->id, $this->admin->id, 'correction', ['ipm'], 'Perbaiki IPM.');
        $workflow->pesantrenSubmitStage1Correction($akreditasi->id, ['ipm' => ['santri_mukim' => 100]]);

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Batas siklus koreksi tahap 1 telah tercapai (1x).');

        $workflow->adminStage1Review($akreditasi->id, $this->admin->id, 'correction', ['sdm'], 'Perbaiki SDM.');
    }

    public function test_stage_2_correction_limit_uses_super_admin_setting_key(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::MAX_SIKLUS_TAHAP2,
            'value' => 1,
        ]);

        $ketuaAsesor = User::factory()->create(['role_id' => 2]);
        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);

        Assessment::create([
            'akreditasi_id' => $akreditasi->id,
            'asesor_id' => $ketuaAsesor->id,
            'tipe' => 'ketua',
        ]);

        $workflow = app(AkreditasiWorkflowService::class);

        $workflow->ketuaAsesorStage2Review($akreditasi->id, $ketuaAsesor->id, 'correction', ['edpm'], 'Perbaiki EDPM.');
        $workflow->pesantrenSubmitStage2Correction($akreditasi->id, ['edpm' => ['status' => 'lengkap']]);

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Batas siklus koreksi tahap 2 telah tercapai (1x).');

        $workflow->ketuaAsesorStage2Review($akreditasi->id, $ketuaAsesor->id, 'correction', ['sdm'], 'Perbaiki SDM.');
    }

    public function test_action_on_limit_auto_approve_controls_default_limit_decision(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::ACTION_ON_LIMIT,
            'value' => 'auto_approve',
        ]);

        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW);

        $result = app(AkreditasiWorkflowService::class)
            ->adminHandleStage1Limit($akreditasi->id, $this->admin->id);

        $this->assertSame(Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, $result->status);
    }

    public function test_action_on_limit_freeze_blocks_default_limit_decision(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::ACTION_ON_LIMIT,
            'value' => 'freeze',
        ]);

        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW);

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Pengajuan dibekukan karena batas siklus koreksi tercapai.');

        app(AkreditasiWorkflowService::class)->adminHandleStage1Limit($akreditasi->id, $this->admin->id);
    }

    public function test_banding_eligibility_disabled_blocks_banding_submission(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::BANDING_ELIGIBILITY,
            'value' => 'disabled',
        ]);

        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_FINAL_REJECTED);
        $akreditasi->forceFill(['status_changed_at' => now()])->save();

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Pengajuan banding sedang dinonaktifkan oleh pengaturan Super Admin.');

        app(BandingService::class)->createBanding($akreditasi->id, $pesantren->id, 'Mohon banding.');
    }

    public function test_nv_override_allowed_setting_blocks_manual_nv_override(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::NV_OVERRIDE_ALLOWED,
            'value' => '0',
        ]);

        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION);
        $butir = $this->createEdpmButir();

        AkreditasiEdpm::create([
            'akreditasi_id' => $akreditasi->id,
            'asesor_id' => $this->admin->id,
            'butir_id' => $butir->id,
            'value' => 3,
            'type' => 'nk',
        ]);

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Override nilai NV tidak diizinkan oleh pengaturan Super Admin.');

        app(AkreditasiWorkflowService::class)
            ->adminValidasiAkhir($akreditasi->id, $this->admin->id, true, 'Override manual.', [
                $butir->id => 4,
            ]);
    }

    public function test_updating_setting_clears_cached_setting_value(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::REVIEW_AWAL_DEADLINE,
            'value' => 5,
        ]);

        $this->assertSame(5, SuperAdminSettings::int(SuperAdminSettings::REVIEW_AWAL_DEADLINE));

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.settings.update'), [
                'key' => SuperAdminSettings::REVIEW_AWAL_DEADLINE,
                'value' => '8',
                'reason' => 'Menyesuaikan SLA review awal.',
            ]);

        $this->assertSame(8, SuperAdminSettings::int(SuperAdminSettings::REVIEW_AWAL_DEADLINE));
    }

    private function createAkreditasi(User $user, string $status): Akreditasi
    {
        return Akreditasi::create([
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
        ]);
    }

    private function createEdpmButir(): MasterEdpmButir
    {
        $komponen = MasterEdpmKomponen::create([
            'kode' => 'KOMP-NV',
            'nama' => 'Komponen NV',
        ]);

        return MasterEdpmButir::create([
            'komponen_id' => $komponen->id,
            'kode' => 'NV.1',
            'nama' => 'Butir NV',
        ]);
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}
