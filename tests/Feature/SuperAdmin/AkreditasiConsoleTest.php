<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Banding;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
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
}
