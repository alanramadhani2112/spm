<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Assessment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssessorWorkloadTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role_id' => 4]);
    }

    public function test_super_admin_can_view_assessor_workload_center(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Workload']);
        $busyAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Sibuk', 'email' => 'sibuk@test.com']);
        $normalAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Siap', 'email' => 'siap@test.com']);

        $this->createAssignments($busyAssessor, $pesantren, 1, 4);
        $this->createAssignments($normalAssessor, $pesantren, 0, 1);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.index'))
            ->assertOk()
            ->assertSee('Beban Kerja Asesor')
            ->assertSee('Asesor Sibuk')
            ->assertSee('Aktif 5')
            ->assertSee('Overload')
            ->assertSee('Ketua 1')
            ->assertSee('Anggota 4')
            ->assertSee('Asesor Siap')
            ->assertSee('Aktif 1')
            ->assertSee('Assignment Aktif')
            ->assertSee('Lihat Workload')
            ->assertSee('/superadmin/asesor-workload/'.$busyAssessor->id, false);
    }

    public function test_assessor_workload_excludes_terminal_assignments(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3]);
        $assessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Aktif']);
        $activeAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);
        $completedAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_COMPLETED);

        Assessment::create([
            'akreditasi_id' => $activeAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'ketua',
        ]);
        Assessment::create([
            'akreditasi_id' => $completedAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'anggota',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.index'))
            ->assertOk()
            ->assertSee('Asesor Aktif')
            ->assertSee('Aktif 1')
            ->assertSee('Ketua 1')
            ->assertSee('Anggota 0')
            ->assertDontSee('Selesai: 1');
    }

    public function test_super_admin_can_filter_overloaded_assessors(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3]);
        $busyAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Overload']);
        $normalAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Normal']);

        $this->createAssignments($busyAssessor, $pesantren, 2, 3);
        $this->createAssignments($normalAssessor, $pesantren, 0, 1);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.index', ['load' => 'high']))
            ->assertOk()
            ->assertSee('Asesor Overload')
            ->assertDontSee('Asesor Normal');
    }

    public function test_super_admin_can_export_filtered_assessor_workload(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3]);
        $busyAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Export']);
        $normalAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Non Export']);

        $this->createAssignments($busyAssessor, $pesantren, 2, 3);
        $this->createAssignments($normalAssessor, $pesantren, 0, 1);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.index', ['load' => 'high']))
            ->assertOk()
            ->assertSee('Ekspor CSV');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.export', ['load' => 'high']))
            ->assertOk()
            ->assertDownload('asesor-workload-superadmin.csv');

        $auditLog = AkreditasiAuditLog::where('action_type', 'superadmin_exported')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('assessor_workload', $auditLog->metadata['export_type']);
        $this->assertSame('csv', $auditLog->metadata['format']);
        $this->assertSame('high', $auditLog->metadata['filters']['load']);
        $this->assertSame(1, $auditLog->metadata['rows_exported']);
    }

    public function test_super_admin_can_view_assessor_workload_detail_page(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Detil']);
        $assessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Detail', 'email' => 'detail@test.com']);
        $firstAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);
        $secondAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_POST_VISITASI_SCORING, ['assessment_deadline' => now()->subDay()]);

        Assessment::create([
            'akreditasi_id' => $firstAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'ketua',
        ]);
        Assessment::create([
            'akreditasi_id' => $secondAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'anggota',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.show', ['asesor' => $assessor->id, 'period' => 'all']))
            ->assertOk()
            ->assertSee('Detail Beban Kerja')
            ->assertSee('Asesor Detail')
            ->assertSee('detail@test.com')
            ->assertSee('Assignment Aktif')
            ->assertSee('Ketua')
            ->assertSee('Anggota')
            ->assertSee('Overdue')
            ->assertSee('Pesantren Detil')
            ->assertSee('Normal');
    }

    public function test_assessor_workload_detail_page_shows_assignment_history_for_selected_assessor_only(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Histori']);
        $selectedAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Histori']);
        $otherAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Lain']);
        $previousAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Lama']);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);
        $anotherAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);

        Assessment::create([
            'akreditasi_id' => $akreditasi->id,
            'asesor_id' => $selectedAssessor->id,
            'tipe' => 'ketua',
        ]);

        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $this->superAdmin->id,
            'actor_user_id' => $this->superAdmin->id,
            'action_type' => 'asesor_assigned',
            'reason' => 'Redistribusi beban kerja asesor.',
            'metadata' => [
                'assignment_context' => 'superadmin_reassign',
                'ketua_id' => $selectedAssessor->id,
                'anggota_ids' => [$otherAssessor->id],
                'previous_assignments' => [
                    [
                        'asesor_id' => $previousAssessor->id,
                        'name' => $previousAssessor->name,
                        'email' => $previousAssessor->email,
                        'tipe' => 'ketua',
                    ],
                ],
                'overload_warnings' => [
                    ['id' => $selectedAssessor->id, 'name' => $selectedAssessor->name],
                ],
            ],
            'created_at' => now(),
        ]);

        AkreditasiAuditLog::create([
            'akreditasi_id' => $anotherAkreditasi->id,
            'user_id' => $this->superAdmin->id,
            'actor_user_id' => $this->superAdmin->id,
            'action_type' => 'asesor_assigned',
            'reason' => 'Log untuk asesor lain.',
            'metadata' => [
                'assignment_context' => 'superadmin_assign',
                'ketua_id' => $otherAssessor->id,
                'anggota_ids' => [],
            ],
            'created_at' => now()->subMinute(),
        ]);

        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $this->superAdmin->id,
            'actor_user_id' => $this->superAdmin->id,
            'action_type' => 'asesor_assigned',
            'reason' => 'Log admin legacy tidak boleh muncul di histori super admin.',
            'metadata' => [
                'ketua_id' => $selectedAssessor->id,
                'anggota_ids' => [],
            ],
            'created_at' => now()->subSeconds(30),
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.show', ['asesor' => $selectedAssessor->id]))
            ->assertOk()
            ->assertSee('Riwayat Assignment', false)
            ->assertSee('Reassignment')
            ->assertSee('Pesantren Histori')
            ->assertSee('Redistribusi beban kerja asesor.')
            ->assertSee('Asesor Lain')
            ->assertSee('Asesor Lama')
            ->assertSee('Overload dikonfirmasi untuk Asesor Histori.')
            ->assertDontSee('Log untuk asesor lain.')
            ->assertDontSee('Log admin legacy tidak boleh muncul di histori super admin.');
    }

    public function test_assessor_workload_detail_page_respects_period_filter_for_active_assignments(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Periode']);
        $assessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Periode']);
        $currentAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, ['created_at' => '2026-02-01 09:00:00']);
        $oldAkreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_POST_VISITASI_SCORING, ['created_at' => '2025-02-01 09:00:00']);

        Assessment::create([
            'akreditasi_id' => $currentAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'ketua',
        ]);
        Assessment::create([
            'akreditasi_id' => $oldAkreditasi->id,
            'asesor_id' => $assessor->id,
            'tipe' => 'anggota',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.show', ['asesor' => $assessor->id, 'period' => '2026']))
            ->assertOk()
            ->assertSee('Periode 2026')
            ->assertSee('Assignment Aktif')
            ->assertSee('Asesor ketua')
            ->assertSee('Asesor anggota')
            ->assertSee('>1<', false)
            ->assertSee('>0<', false);
    }

    public function test_super_admin_without_export_permission_cannot_export_assessor_workload(): void
    {
        $this->revokeSuperAdminPermission('superadmin.assessor_workload.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.export'))
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_view_assessor_workload_center(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get(route('superadmin.asesor-workload.index'))
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_view_assessor_workload_detail_page(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $assessor = User::factory()->create(['role_id' => 2]);

        $this->actingAs($admin)
            ->get(route('superadmin.asesor-workload.show', $assessor))
            ->assertForbidden();
    }

    public function test_workload_detail_returns_404_for_non_assessor_user(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.asesor-workload.show', $pesantren))
            ->assertNotFound();
    }

    private function createAssignments(User $assessor, User $pesantren, int $ketuaCount, int $anggotaCount): void
    {
        for ($index = 0; $index < $ketuaCount; $index++) {
            Assessment::create([
                'akreditasi_id' => $this->createAkreditasi($pesantren, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW)->id,
                'asesor_id' => $assessor->id,
                'tipe' => 'ketua',
            ]);
        }

        for ($index = 0; $index < $anggotaCount; $index++) {
            Assessment::create([
                'akreditasi_id' => $this->createAkreditasi($pesantren, Akreditasi::STATUS_POST_VISITASI_SCORING)->id,
                'asesor_id' => $assessor->id,
                'tipe' => 'anggota',
            ]);
        }
    }

    private function createAkreditasi(User $pesantren, string $status, array $overrides = []): Akreditasi
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
            'status_changed_at' => now()->subDay(),
            'assessment_deadline' => now()->addWeek(),
        ]);

        if ($overrides !== []) {
            $akreditasi->forceFill($overrides)->save();
        }

        return $akreditasi;
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}





