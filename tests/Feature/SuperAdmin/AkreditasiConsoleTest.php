<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Assessment;
use App\Models\Banding;
use App\Models\Document;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\Role;
use App\Models\SdmPesantren;
use App\Models\SuperAdminSetting;
use App\Models\User;
use App\Services\DocumentService;
use App\Support\SuperAdminSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AkreditasiConsoleTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
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
            ->assertSee('Konsol Akreditasi')
            ->assertSee('Daftar Pengajuan')
            ->assertSee('Status')
            ->assertSee('Pesantren Detail')
            ->assertSee('Detail')
            ->assertSee('Review Awal');
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
            ->assertSee('Tindakan')
            ->assertSee('Langkah berikutnya')
            ->assertSee('Review pengajuan awal')
            ->assertSee('Review Awal')
            ->assertSee('Langkah berikutnya')
            ->assertSee('Data Pesantren')
            ->assertSee('Log Audit')
            ->assertSee('Pesantren Detail');
    }

    public function test_super_admin_detail_displays_direct_action_ctas(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);

        $assessment = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSMENT_OPEN);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $assessment))
            ->assertOk()
            ->assertSee('Tindakan Lain')
            ->assertSee('Tindakan Lain')
            ->assertSee(route('superadmin.akreditasi.upload-kk', $assessment), false);

        $scheduled = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_SCHEDULED);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $scheduled))
            ->assertOk()
            ->assertSee('Tindakan Lain')
            ->assertSee(route('superadmin.akreditasi.tandai-visitasi-selesai', $scheduled), false);

        $scoring = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $scoring))
            ->assertOk()
            ->assertSee('Tindakan Lain')
            ->assertSee(route('superadmin.akreditasi.submit-hasil-visitasi', $scoring), false);
    }

    public function test_super_admin_can_run_direct_action_endpoints(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);

        $assessment = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSMENT_OPEN);
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.upload-kk', $assessment), [
                'file' => UploadedFile::fake()->create('kartu-kendali.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));
        $this->assertDatabaseHas('documents', [
            'akreditasi_id' => $assessment->id,
            'type' => DocumentService::TYPE_KARTU_KENDALI,
            'uploaded_by_user_id' => $this->superAdmin->id,
        ]);

        $scheduled = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_SCHEDULED);
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.tandai-visitasi-selesai', $scheduled))
            ->assertRedirect(route('superadmin.akreditasi.index'));
        $this->assertSame(Akreditasi::STATUS_POST_VISITASI_SCORING, $scheduled->fresh()->status);

        $scoring = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
            'is_na1_final' => true,
            'is_na2_final' => true,
            'is_nk_final' => true,
        ]);
        Document::create(['akreditasi_id' => $scoring->id, 'type' => DocumentService::TYPE_KARTU_KENDALI, 'file_path' => 'documents/kk.pdf', 'uploaded_by_user_id' => $this->superAdmin->id]);
        Document::create(['akreditasi_id' => $scoring->id, 'type' => DocumentService::TYPE_LAPORAN_ASESOR, 'file_path' => 'documents/laporan.pdf', 'uploaded_by_user_id' => $this->superAdmin->id]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.submit-hasil-visitasi', $scoring))
            ->assertRedirect(route('superadmin.akreditasi.index'));
        $this->assertSame(Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, $scoring->fresh()->status);
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
            ->assertSee('Proses Akreditasi')
            ->assertSee('Review Asesor Tahap 2')
            ->assertSee('Review Asesor Tahap 2');
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
            ->assertSee('Belum ada pesantren terdaftar');
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

        $exportTypes = AkreditasiAuditLog::where('action_type', 'superadmin_exported')
            ->get()
            ->pluck('metadata.export_type')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['akreditasi_scores', 'document_status'], $exportTypes);
        $this->assertSame($akreditasi->id, Akreditasi::where('uuid', $akreditasi->uuid)->firstOrFail()->id);
    }

    public function test_super_admin_without_akreditasi_export_permission_cannot_export_akreditasi_console(): void
    {
        $this->revokeSuperAdminPermission('superadmin.akreditasi.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export'))
            ->assertForbidden();
    }

    public function test_super_admin_without_scores_export_permission_cannot_export_scores(): void
    {
        $this->revokeSuperAdminPermission('superadmin.akreditasi_scores.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-scores'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-documents'))
            ->assertOk()
            ->assertDownload('dokumen-status-superadmin.csv');
    }

    public function test_super_admin_without_document_export_permission_cannot_export_document_status(): void
    {
        $this->revokeSuperAdminPermission('superadmin.akreditasi_documents.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-documents'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.export-scores'))
            ->assertOk()
            ->assertDownload('nilai-peringkat-superadmin.csv');
    }

    public function test_super_admin_without_sk_export_permission_cannot_export_sk_management(): void
    {
        $this->revokeSuperAdminPermission('superadmin.sk.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.sk.export'))
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

    public function test_super_admin_buka_assessment_uses_default_setting_when_deadline_empty(): void
    {
        SuperAdminSetting::create([
            'key' => SuperAdminSettings::ASSESSMENT_DEADLINE,
            'value' => 10,
        ]);
        $this->travelTo(now()->startOfDay());

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSMENT_OPEN);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.buka-assessment', $akreditasi->id))
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->assertSame(now()->addDays(10)->toDateString(), $akreditasi->fresh()->assessment_deadline?->toDateString());
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
            ->assertSee('Beban Kerja Asesor')
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
        $reassignAkreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);
        Assessment::create([
            'akreditasi_id' => $reassignAkreditasi->id,
            'asesor_id' => User::factory()->create(['role_id' => 2])->id,
            'tipe' => 'ketua',
        ]);

        $routes = [
            route('superadmin.akreditasi.review-awal', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_INITIAL_SUBMITTED)),
            route('superadmin.akreditasi.buka-assessment', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSMENT_OPEN)),
            route('superadmin.akreditasi.review-tahap1', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW)),
            route('superadmin.akreditasi.assign-asesor', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_ASSIGNMENT)),
            route('superadmin.akreditasi.reassign-asesor', $reassignAkreditasi),
            route('superadmin.akreditasi.review-tahap2', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW)),
            route('superadmin.akreditasi.jadwalkan-visitasi', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_SCHEDULED)),
            route('superadmin.akreditasi.input-na1', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.input-na2', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.input-nk', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING)),
            route('superadmin.akreditasi.upload-laporan', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_VISITASI_COMPLETED)),
            route('superadmin.akreditasi.validasi-akhir', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION)),
            route('superadmin.akreditasi.form-terbitkan-sk', $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_FINAL_APPROVED)),
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

    public function test_super_admin_shared_workflow_views_use_superadmin_form_routes(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $asesor = User::factory()->create(['role_id' => 2]);

        $assessment = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSMENT_OPEN);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.buka-assessment', $assessment))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.buka-assessment', $assessment), false)
            ->assertDontSee(route('admin.akreditasi.buka-assessment', $assessment), false);

        $assignment = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_ASSIGNMENT);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.assign-asesor', $assignment))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.assign-asesor', $assignment), false)
            ->assertDontSee(route('admin.akreditasi.assign-asesor', $assignment), false);

        $reassignment = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);
        Assessment::create([
            'akreditasi_id' => $reassignment->id,
            'asesor_id' => $asesor->id,
            'tipe' => 'ketua',
        ]);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.reassign-asesor', $reassignment))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.reassign-asesor', $reassignment), false)
            ->assertDontSee(route('admin.akreditasi.reassign-asesor', $reassignment), false);

        $sk = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_FINAL_APPROVED);
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.form-terbitkan-sk', $sk))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.terbitkan-sk', $sk), false)
            ->assertDontSee(route('admin.akreditasi.terbitkan-sk', $sk), false);
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

    public function test_super_admin_can_view_visitasi_overview(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Visitasi']);
        $this->createCompletePesantrenData($pesantrenUser);
        $ketua = User::factory()->create(['role_id' => 2, 'name' => 'Ketua Visitasi']);
        $anggota = User::factory()->create(['role_id' => 2, 'name' => 'Anggota Visitasi']);

        $scheduled = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_VISITASI_SCHEDULED,
            'tgl_visitasi' => now()->addDay(),
            'tgl_visitasi_akhir' => now()->addDays(2),
            'catatan_visitasi' => 'Visitasi tahap pertama.',
        ]);
        $scoring = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
            'na1' => 85,
            'laporan_visitasi_asesor1' => 'laporan/a1.pdf',
        ]);
        $validation = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
        ]);

        foreach ([$scheduled, $scoring, $validation] as $akreditasi) {
            Assessment::create([
                'akreditasi_id' => $akreditasi->id,
                'asesor_id' => $ketua->id,
                'tipe' => 'ketua',
            ]);
            Assessment::create([
                'akreditasi_id' => $akreditasi->id,
                'asesor_id' => $anggota->id,
                'tipe' => 'anggota',
            ]);
        }

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.visitasi.index'))
            ->assertOk()
            ->assertSee('Visitasi')
            ->assertSee('Siap Dijadwalkan')
            ->assertSee('Terjadwal')
            ->assertSee('Daftar Visitasi')
            ->assertSee('Pesantren Detail')
            ->assertSee('Ketua Visitasi')
            ->assertSee('Anggota Visitasi')
            ->assertSee('Visitasi tahap pertama.')
            ->assertSee('Perbarui Jadwal')
            ->assertSee(route('superadmin.akreditasi.jadwalkan-visitasi', $scheduled), false)
            ->assertSee(route('superadmin.akreditasi.input-na1', $scoring), false)
            ->assertSee(route('superadmin.akreditasi.validasi-akhir', $validation), false);
    }

    public function test_visitasi_overview_filters_by_status_and_schedule(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Filter Visitasi']);
        $scheduled = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'VIS-SCHEDULED',
            'status' => Akreditasi::STATUS_VISITASI_SCHEDULED,
            'tgl_visitasi' => now()->subDays(3),
            'tgl_visitasi_akhir' => now()->subDay(),
        ]);
        $scoring = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'VIS-SCORING',
            'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.visitasi.index', ['status' => Akreditasi::STATUS_POST_VISITASI_SCORING]))
            ->assertOk()
            ->assertSee('VIS-SCORING')
            ->assertDontSee('VIS-SCHEDULED');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.visitasi.index', ['schedule' => 'past_due']))
            ->assertOk()
            ->assertSee('VIS-SCHEDULED')
            ->assertDontSee('VIS-SCORING');
    }

    public function test_visitasi_overview_searches_by_uuid_and_respects_permission(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Search Visitasi']);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'VIS-SEARCH-001',
            'status' => Akreditasi::STATUS_VISITASI_SCHEDULED,
            'tgl_visitasi' => now()->addDays(5),
            'tgl_visitasi_akhir' => now()->addDays(6),
        ]);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'VIS-HIDDEN-002',
            'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.visitasi.index', ['q' => 'SEARCH-001']))
            ->assertOk()
            ->assertSee('VIS-SEARCH-001')
            ->assertDontSee('VIS-HIDDEN-002');

        $restricted = User::factory()->create(['role_id' => 4]);
        $this->revokeSuperAdminPermission('akreditasi.visitasi.manage');

        $this->actingAs($restricted)
            ->get(route('superadmin.visitasi.index'))
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

    public function test_super_admin_can_submit_scoring_without_assessor_assignment(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING);
        $butir = $this->createScoringButir();

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.input-na1', $akreditasi), [
                'butir' => [$butir->id => 90],
                'set_final' => '1',
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.input-na2', $akreditasi), [
                'butir' => [$butir->id => 86],
                'set_final' => '1',
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.input-nk', $akreditasi), [
                'butir' => [$butir->id => 88],
                'set_final' => '1',
            ])
            ->assertRedirect(route('superadmin.akreditasi.index'));

        $akreditasi->refresh();
        $this->assertTrue($akreditasi->is_na1_final);
        $this->assertTrue($akreditasi->is_na2_final);
        $this->assertTrue($akreditasi->is_nk_final);
        $this->assertSame('90.00', $akreditasi->na1);
        $this->assertSame('86.00', $akreditasi->na2);
        $this->assertSame('88.00', $akreditasi->nk);
    }

    public function test_super_admin_scoring_rejects_invalid_score_value(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING);
        $butir = $this->createScoringButir();

        $this->actingAs($this->superAdmin)
            ->from(route('superadmin.akreditasi.input-na1', $akreditasi))
            ->post(route('superadmin.akreditasi.input-na1', $akreditasi), [
                'butir' => [$butir->id => 101],
                'set_final' => '1',
            ])
            ->assertRedirect(route('superadmin.akreditasi.input-na1', $akreditasi))
            ->assertSessionHasErrors("butir.{$butir->id}");

        $this->assertFalse($akreditasi->fresh()->is_na1_final);
    }

    public function test_super_admin_cannot_submit_nk_before_na1_and_na2_final(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = $this->createAkreditasi($pesantrenUser, Akreditasi::STATUS_POST_VISITASI_SCORING);
        $butir = $this->createScoringButir();

        $this->actingAs($this->superAdmin)
            ->from(route('superadmin.akreditasi.input-nk', $akreditasi))
            ->post(route('superadmin.akreditasi.input-nk', $akreditasi), [
                'butir' => [$butir->id => 88],
                'set_final' => '1',
            ])
            ->assertRedirect(route('superadmin.akreditasi.input-nk', $akreditasi))
            ->assertSessionHas('error', 'Nilai NK hanya dapat diinput setelah NA1 dan NA2 ditetapkan final.');

        $this->assertFalse($akreditasi->fresh()->is_nk_final);
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

    public function test_super_admin_can_open_and_submit_sk_publish_flow(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_FINAL_APPROVED,
            'nilai' => 91.25,
            'peringkat' => 'A',
            'nv' => 3.65,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi->id))
            ->assertOk()
            ->assertSee('Terbitkan SK Akreditasi')
            ->assertSee(route('superadmin.akreditasi.terbitkan-sk', $akreditasi->id), false);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.terbitkan-sk', $akreditasi->id), [
                'nomor_sk' => 'SK-TEST-001',
                'masa_berlaku' => '2026-07-01',
                'sertifikat_file' => UploadedFile::fake()->create('sertifikat.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('superadmin.akreditasi.show', $akreditasi->id));

        $this->assertDatabaseHas('akreditasis', [
            'id' => $akreditasi->id,
            'status' => Akreditasi::STATUS_COMPLETED,
            'nomor_sk' => 'SK-TEST-001',
            'masa_berlaku' => '2026-07-01 00:00:00',
            'masa_berlaku_akhir' => '2031-06-30 00:00:00',
        ]);
        $this->assertDatabaseHas('documents', [
            'akreditasi_id' => $akreditasi->id,
            'type' => DocumentService::TYPE_SERTIFIKAT,
        ]);
        $akreditasi->refresh();
        $this->assertNotNull($akreditasi->sertifikat_path);
        Storage::disk('local')->assertExists($akreditasi->sertifikat_path);
    }

    public function test_super_admin_console_and_detail_show_sk_action_and_metadata(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-MANAGEMENT-001',
            'status' => Akreditasi::STATUS_FINAL_APPROVED,
        ]);
        $completed = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-MANAGEMENT-002',
            'status' => Akreditasi::STATUS_COMPLETED,
            'nomor_sk' => 'SK/2026/002',
            'masa_berlaku' => '2026-07-01',
            'masa_berlaku_akhir' => '2031-06-30',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_FINAL_APPROVED, 'q' => 'SK-MANAGEMENT-001']))
            ->assertOk()
            ->assertSee(route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi->id), false)
            ->assertSee('Terbitkan SK');

        $completed->forceFill([
            'sertifikat_path' => 'documents/sertifikat/demo.pdf',
        ])->save();
        \App\Models\Document::create([
            'akreditasi_id' => $completed->id,
            'type' => DocumentService::TYPE_SERTIFIKAT,
            'file_path' => 'documents/sertifikat/demo.pdf',
            'uploaded_by_user_id' => $this->superAdmin->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $completed->id))
            ->assertOk()
            ->assertSee('Status SK')
            ->assertSee('Tersedia')
            ->assertSee('SK/2026/002')
            ->assertSee('01 Jul 2026')
            ->assertSee('30 Jun 2031')
            ->assertSee(route('superadmin.akreditasi.sertifikat.download', $completed), false);
    }

    public function test_super_admin_can_open_sk_management_page(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren SK Center']);
        $ready = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-CENTER-READY',
            'status' => Akreditasi::STATUS_FINAL_APPROVED,
            'nilai' => 91,
            'peringkat' => 'A',
        ]);
        $published = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-CENTER-PUBLISHED',
            'status' => Akreditasi::STATUS_COMPLETED,
            'nomor_sk' => 'SK/2026/003',
            'masa_berlaku' => '2026-08-01',
            'masa_berlaku_akhir' => '2031-07-31',
            'sertifikat_path' => 'documents/sertifikat/sk-center.pdf',
        ]);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-CENTER-MISSING-CERTIFICATE',
            'status' => Akreditasi::STATUS_COMPLETED,
            'nomor_sk' => 'SK/2026/004',
            'masa_berlaku' => '2026-08-01',
            'masa_berlaku_akhir' => '2031-07-31',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.sk.index', ['q' => 'SK-CENTER']))
            ->assertOk()
            ->assertSeeText('Manajemen SK')
            ->assertSeeText('Command Center SK')
            ->assertSeeText('Fokus Siap Terbit')
            ->assertSeeText('Perlu penerbitan SK')
            ->assertSeeText('Terbitkan SK sekarang')
            ->assertSeeText('Belum Ada Sertifikat')
            ->assertSeeText('Lengkapi sertifikat digital')
            ->assertSee('SK-CENTER-READY')
            ->assertSee('SK/2026/003')
            ->assertSee(route('superadmin.akreditasi.form-terbitkan-sk', $ready), false)
            ->assertSee(route('superadmin.akreditasi.sertifikat.download', $published), false);
    }

    public function test_super_admin_can_export_sk_management_with_audit_log(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Export SK']);
        Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => 'SK-EXPORT-001',
            'status' => Akreditasi::STATUS_COMPLETED,
            'nomor_sk' => 'SK/EXPORT/001',
            'masa_berlaku' => '2026-09-01',
            'masa_berlaku_akhir' => '2031-08-31',
            'sertifikat_path' => 'documents/sertifikat/export.pdf',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.sk.export', ['status' => 'published', 'certificate' => 'with', 'q' => 'SK-EXPORT']))
            ->assertOk()
            ->assertDownload('sk-management-superadmin.csv');

        $auditLog = AkreditasiAuditLog::where('action_type', 'superadmin_exported')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('sk_management', $auditLog->metadata['export_type']);
        $this->assertSame('published', $auditLog->metadata['filters']['status']);
        $this->assertSame('with', $auditLog->metadata['filters']['certificate']);
        $this->assertSame(1, $auditLog->metadata['rows_exported']);
    }

    public function test_super_admin_without_sk_export_permission_cannot_export_sk_management_from_filtered_view(): void
    {
        $this->revokeSuperAdminPermission('superadmin.sk.export');

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.sk.export'))
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
            ->get(route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi->id))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.akreditasi.terbitkan-sk', $akreditasi->id), [
                'nomor_sk' => 'SK-TEST-001',
                'masa_berlaku' => '2026-07-01',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_cannot_open_sk_form_for_wrong_status(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi->id))
            ->assertNotFound();
    }

    public function test_completed_akreditasi_shows_no_action_state_in_action_center(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $this->createCompletePesantrenData($pesantrenUser);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.akreditasi.show', $akreditasi->id))
            ->assertOk()
            ->assertSee('Tindakan')
            ->assertSee('Tidak ada aksi Super Admin untuk status ini.');
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

    private function createScoringButir(): MasterEdpmButir
    {
        $komponen = MasterEdpmKomponen::create([
            'kode' => 'SKOR_DEMO',
            'nama' => 'Komponen Skoring Demo',
        ]);

        return MasterEdpmButir::create([
            'komponen_id' => $komponen->id,
            'kode' => 'SKOR.1',
            'nama' => 'Butir Skoring Demo',
        ]);
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}









