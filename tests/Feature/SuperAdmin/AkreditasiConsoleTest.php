<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Assessment;
use App\Models\Banding;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Permission;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\Role;
use App\Models\SdmPesantren;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AkreditasiConsoleTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role_id' => 4]);
    }

    public function test_super_admin_can_view_akreditasi_console_index(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Console']);
        $this->createCompletePesantrenData($pesantrenUser);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.index'))
            ->assertOk()
            ->assertSee('Workflow Console Akreditasi')
            ->assertSee('Gunakan status sebagai petunjuk aksi berikutnya')
            ->assertSee('Status dan Langkah Berikutnya')
            ->assertSee('Pesantren Detail')
            ->assertSee('Detail');
    }

    public function test_super_admin_can_view_akreditasi_detail_console(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Detail']);
        $this->createCompletePesantrenData($pesantrenUser);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);
        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $this->superAdmin->id,
            'actor_user_id' => $this->superAdmin->id,
            'action_type' => 'status_changed',
            'from_status' => Akreditasi::STATUS_DRAFT_PROFILE,
            'to_status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
            'created_at' => now(),
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $akreditasi->id))
            ->assertOk()
            ->assertSee('Action Center')
            ->assertSee('Data Pesantren')
            ->assertSee('Audit Timeline')
            ->assertSee('Review Awal')
            ->assertSee('Pesantren Detail');
    }

    public function test_super_admin_detail_displays_assessor_assignment_history(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren History']);
        $oldAssessor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Lama']);
        $newKetua = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Ketua Baru']);
        $newAnggota = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Anggota Baru']);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ]);

        Assessment::create([
            'akreditasi_id' => $akreditasi->id,
            'asesor_id' => $newKetua->id,
            'tipe' => 'ketua',
        ]);
        AkreditasiAuditLog::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $this->superAdmin->id,
            'actor_user_id' => $this->superAdmin->id,
            'action_type' => 'asesor_assigned',
            'reason' => 'Redistribusi karena beban kerja.',
            'metadata' => [
                'assignment_context' => 'superadmin_reassign',
                'ketua_id' => $newKetua->id,
                'anggota_ids' => [$newAnggota->id],
                'previous_assignments' => [
                    [
                        'asesor_id' => $oldAssessor->id,
                        'name' => 'Asesor Lama',
                        'email' => $oldAssessor->email,
                        'tipe' => 'ketua',
                    ],
                ],
                'overload_warnings' => [
                    ['id' => $newKetua->id, 'name' => 'Asesor Ketua Baru'],
                ],
            ],
            'created_at' => now(),
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $akreditasi->id))
            ->assertOk()
            ->assertSeeText('Riwayat Assignment Asesor')
            ->assertSeeText('Reassignment')
            ->assertSeeText('Asesor Lama')
            ->assertSeeText('Asesor Ketua Baru')
            ->assertSeeText('Asesor Anggota Baru')
            ->assertSeeText('Redistribusi karena beban kerja.')
            ->assertSeeText('Overload dikonfirmasi');
    }

    public function test_non_super_admin_cannot_view_console_detail(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($admin)
            ->get(route('superadmin.akreditasi.show', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_pengajuan_route_still_renders_after_detail_route_added(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.pengajuan'))
            ->assertOk()
            ->assertSee('Pilih Pesantren');
    }

    public function test_super_admin_can_submit_pengajuan_for_complete_pesantren(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $this->createCompletePesantrenData($pesantrenUser);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.submit-pengajuan'), [
                'pesantren_id' => $pesantrenUser->id,
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->assertDatabaseHas('akreditasis', [
            'user_id' => $pesantrenUser->id,
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);
    }

    public function test_super_admin_can_export_akreditasi_console_csv(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Export']);
        $this->createCompletePesantrenData($pesantrenUser);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export', ['status' => Akreditasi::STATUS_INITIAL_SUBMITTED, 'q' => 'Export']))
            ->assertOk()
            ->assertDownload('akreditasi-superadmin.csv');

        $auditLog = AkreditasiAuditLog::where('action_type', 'superadmin_exported')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertNull($auditLog->akreditasi_id);
        $this->assertSame('akreditasi_console', $auditLog->metadata['export_type']);
        $this->assertSame('csv', $auditLog->metadata['format']);
        $this->assertSame('all', $auditLog->metadata['filters']['period']);
        $this->assertSame(Akreditasi::STATUS_INITIAL_SUBMITTED, $auditLog->metadata['filters']['status']);
        $this->assertSame('Export', $auditLog->metadata['filters']['q']);
        $this->assertSame(1, $auditLog->metadata['rows_exported']);
    }

    public function test_super_admin_can_export_scores_and_document_status(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Report']);
        Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Report',
            'dok_profil' => 'dok/profil.pdf',
        ]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
            'na1' => 90,
            'na2' => 88,
            'nk' => 91,
            'nv' => 89,
            'nilai' => 89.5,
            'peringkat' => 'A',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-scores', ['q' => 'Pesantren Report']))
            ->assertOk()
            ->assertDownload('nilai-peringkat-superadmin.csv');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-documents', ['q' => 'Pesantren Report']))
            ->assertOk()
            ->assertDownload('dokumen-status-superadmin.csv');

        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'action_type' => 'superadmin_exported',
            'user_id' => $this->superAdmin->id,
        ]);
        $this->assertSame(2, AkreditasiAuditLog::where('action_type', 'superadmin_exported')->count());
        $this->assertSame($akreditasi->id, Akreditasi::where('uuid', $akreditasi->uuid)->firstOrFail()->id);
    }

    public function test_super_admin_without_export_permission_cannot_export_akreditasi_console(): void
    {
        $this->revokeSuperAdminPermission('superadmin.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_banding_page_with_superadmin_actions(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_APPEAL_SUBMITTED,
        ]);
        $banding = Banding::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $pesantrenUser->id,
            'reason' => 'Mohon peninjauan superadmin.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.banding', $akreditasi->id))
            ->assertOk()
            ->assertSee('Review Permohonan Banding')
            ->assertSee('Mohon peninjauan superadmin.')
            ->assertSee(route('superadmin.banding.terima', $banding->id), false)
            ->assertSee(route('superadmin.banding.tolak', $banding->id), false)
            ->assertDontSee(route('admin.banding.terima', $banding->id), false);
    }

    public function test_super_admin_can_accept_banding_from_dedicated_route(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_APPEAL_SUBMITTED,
        ]);
        $banding = Banding::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $pesantrenUser->id,
            'reason' => 'Mohon peninjauan ulang.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.banding.terima', $banding->id), [
                'response' => 'Banding diterima oleh superadmin.',
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->assertDatabaseHas('bandings', [
            'id' => $banding->id,
            'status' => 'accepted',
            'processed_by' => $this->superAdmin->id,
            'admin_response' => 'Banding diterima oleh superadmin.',
        ]);
        $this->assertDatabaseHas('akreditasis', [
            'id' => $akreditasi->id,
            'status' => Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ]);
    }

    public function test_super_admin_review_awal_uses_superadmin_form_routes(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.review-awal', $akreditasi->id))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.terima-pengajuan', $akreditasi->id), false)
            ->assertSee(route('superadmin.akreditasi.tolak-pengajuan', $akreditasi->id), false)
            ->assertDontSee(route('admin.akreditasi.terima-pengajuan', $akreditasi->id), false);
    }

    public function test_super_admin_review_tahap2_uses_superadmin_form_routes(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.review-tahap2', $akreditasi->id))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.layak-visitasi', $akreditasi->id), false)
            ->assertSee(route('superadmin.akreditasi.minta-perbaikan-tahap2', $akreditasi->id), false)
            ->assertDontSee(route('asesor.ketua.nyatakan-layak-visitasi', $akreditasi->id), false);
    }

    public function test_super_admin_assign_asesor_page_shows_active_workload(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $asesor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Sibuk', 'email' => 'sibuk@test.com']);
        $activeAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ]);
        $completedAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
        ]);
        $targetAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);

        Assessment::create([
            'akreditasi_id' => $activeAkreditasi->id,
            'asesor_id' => $asesor->id,
            'tipe' => 'ketua',
        ]);
        Assessment::create([
            'akreditasi_id' => $completedAkreditasi->id,
            'asesor_id' => $asesor->id,
            'tipe' => 'anggota',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.assign-asesor', $targetAkreditasi->id))
            ->assertOk()
            ->assertSee('Workload Asesor Aktif')
            ->assertSee('Asesor Sibuk')
            ->assertSee('Aktif 1')
            ->assertSee('K: 1 / A: 0');
    }

    public function test_super_admin_assign_asesor_page_warns_when_selection_can_trigger_overload(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $asesor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Hampir Penuh', 'email' => 'hampir@test.com']);
        $targetAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);

        $this->createActiveAssignments($pesantrenUser, $asesor, 4);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.assign-asesor', $targetAkreditasi->id))
            ->assertOk()
            ->assertSee('Konfirmasi Assignment Overload')
            ->assertSee('Asesor Hampir Penuh: 4 -> 5', false);
    }

    public function test_super_admin_must_confirm_projected_overload_when_assigning_asesor(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $asesor = User::factory()->create(['role_id' => 2]);
        $targetAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);

        $this->createActiveAssignments($pesantrenUser, $asesor, 4);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.assign-asesor', $targetAkreditasi->id), [
                'ketua_id' => $asesor->id,
            ])
            ->assertSessionHasErrors(['overload_confirmation', 'reason']);

        $this->assertDatabaseHas('akreditasis', [
            'id' => $targetAkreditasi->id,
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);
        $this->assertDatabaseMissing('assessments', [
            'akreditasi_id' => $targetAkreditasi->id,
            'asesor_id' => $asesor->id,
        ]);
    }

    public function test_super_admin_can_confirm_overload_assignment_with_audit_reason(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $asesor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Override']);
        $targetAkreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);

        $this->createActiveAssignments($pesantrenUser, $asesor, 4);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.assign-asesor', $targetAkreditasi->id), [
                'ketua_id' => $asesor->id,
                'overload_confirmation' => '1',
                'reason' => 'Distribusi tetap dipilih karena domain keahlian sesuai.',
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->assertDatabaseHas('akreditasis', [
            'id' => $targetAkreditasi->id,
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ]);
        $this->assertDatabaseHas('assessments', [
            'akreditasi_id' => $targetAkreditasi->id,
            'asesor_id' => $asesor->id,
            'tipe' => 'ketua',
        ]);

        $auditLog = AkreditasiAuditLog::where('action_type', 'asesor_assigned')->firstOrFail();
        $this->assertSame('Distribusi tetap dipilih karena domain keahlian sesuai.', $auditLog->reason);
        $this->assertSame('superadmin_assign', $auditLog->metadata['assignment_context']);
        $this->assertTrue($auditLog->metadata['overload_confirmed']);
        $this->assertSame($asesor->id, $auditLog->metadata['ketua_id']);
        $this->assertSame(4, $auditLog->metadata['overload_warnings'][0]['current_total']);
        $this->assertSame(5, $auditLog->metadata['overload_warnings'][0]['projected_total']);
    }

    public function test_super_admin_shared_workflow_views_show_acting_as_banner(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $banner = 'Anda sedang menjalankan flow ini sebagai Super Admin';
        $routes = [
            route('superadmin.akreditasi.review-awal', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_INITIAL_SUBMITTED)),
            route('superadmin.akreditasi.review-tahap1', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW)),
            route('superadmin.akreditasi.review-tahap2', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW)),
            route('superadmin.akreditasi.jadwalkan-visitasi', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_SCHEDULED)),
            route('superadmin.akreditasi.input-na1', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.input-na2', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.input-nk', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.upload-laporan', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_COMPLETED)),
            route('superadmin.akreditasi.validasi-akhir', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION)),
        ];

        $bandingAkreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_APPEAL_SUBMITTED);
        Banding::create([
            'akreditasi_id' => $bandingAkreditasi->id,
            'user_id' => $pesantrenUser->id,
            'reason' => 'Mohon banding.',
            'status' => 'pending',
        ]);
        $routes[] = route('superadmin.akreditasi.banding', $bandingAkreditasi);

        foreach ($routes as $route) {
            $this->actingAs($this->superAdmin)
                ->get($route)
                ->assertOk()
                ->assertSeeText($banner);
        }
    }

    public function test_super_admin_without_final_approval_permission_cannot_approve_final(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.final.approve');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.approve-final', $akreditasi->id), [
                'reason' => 'Final approval test.',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_without_review_awal_permission_cannot_accept_pengajuan(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.review_awal');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.terima-pengajuan', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_stage1_permission_cannot_approve_tahap1(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.stage1_review');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.approve-tahap1', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_assign_asesor_permission_cannot_open_assign_asesor(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.assign_asesor');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.assign-asesor', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_stage2_permission_cannot_open_review_tahap2(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.stage2_review');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.review-tahap2', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_visitasi_permission_cannot_schedule_visitasi(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.visitasi.manage');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_VISITASI_SCHEDULED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.jadwalkan-visitasi', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_scoring_permission_cannot_open_scoring(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.scoring.manage');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.input-na1', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_laporan_permission_cannot_open_laporan_upload(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.laporan.manage');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_VISITASI_COMPLETED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.upload-laporan', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_document_upload_permission_cannot_upload_kartu_kendali(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.document.upload');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSMENT_OPEN,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.upload-kk', $akreditasi->id))
            ->assertForbidden();
    }

    public function test_super_admin_without_final_reject_permission_cannot_reject_final(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.final.reject');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.tolak-final', $akreditasi->id), [
                'reason' => 'Menguji permission final reject.',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_without_banding_process_permission_cannot_accept_banding(): void
    {
        $this->revokeSuperAdminPermission('akreditasi.proses_banding');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_APPEAL_SUBMITTED,
        ]);
        $banding = Banding::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $pesantrenUser->id,
            'reason' => 'Mohon peninjauan ulang.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.banding.terima', $banding->id), [
                'response' => 'Banding diterima oleh superadmin.',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_without_sk_publish_permission_cannot_publish_sk(): void
    {
        $this->revokeSuperAdminPermission('sk.publish');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_FINAL_APPROVED,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.terbitkan-sk', $akreditasi->id), [
                'nomor_sk' => 'SK-TEST-001',
                'masa_berlaku' => '2026-2030',
            ])
            ->assertForbidden();
    }

    private function createAkreditasi(User $user, string $status): Akreditasi
    {
        return Akreditasi::create([
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
        ]);
    }

    private function createCompletePesantrenData(User $user): void
    {
        $pesantren = Pesantren::create([
            'user_id' => $user->id,
            'nama_pesantren' => 'Pesantren Detail',
            'ns_pesantren' => 'NSP-001',
            'alamat' => 'Jl. Pengujian No. 1',
            'layanan_satuan_pendidikan' => ['MTs'],
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
        ]);

        PesantrenUnit::create([
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MTs',
            'jumlah_rombel' => 6,
        ]);

        Ipm::create(['user_id' => $user->id, 'data' => ['santri_mukim' => 100]]);
        SdmPesantren::create(['user_id' => $user->id, 'data' => ['ustaz_tetap' => 12]]);
        Edpm::create(['user_id' => $user->id, 'data' => ['self_assessment' => 'lengkap']]);
    }

    private function createActiveAssignments(User $pesantrenUser, User $asesor, int $count): void
    {
        for ($index = 0; $index < $count; $index++) {
            $akreditasi = Akreditasi::create([
                'user_id' => $pesantrenUser->id,
                'uuid' => (string) Str::uuid(),
                'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            ]);

            Assessment::create([
                'akreditasi_id' => $akreditasi->id,
                'asesor_id' => $asesor->id,
                'tipe' => $index === 0 ? 'ketua' : 'anggota',
            ]);
        }
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}
