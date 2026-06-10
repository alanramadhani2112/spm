<?php

namespace Tests\Feature\Asesor;

use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AsesorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $ketuaAsesor;
    private User $anggotaAsesor;
    private User $pesantrenUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ketuaAsesor = User::factory()->create(['role_id' => 2, 'name' => 'Ketua Asesor', 'email' => 'ketua@test.com']);
        $this->anggotaAsesor = User::factory()->create(['role_id' => 2, 'name' => 'Anggota Asesor', 'email' => 'anggota@test.com']);
        $this->pesantrenUser = User::factory()->create(['role_id' => 3]);
    }

    private function createAkreditasi(string $status): Akreditasi
    {
        return Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
        ]);
    }

    public function test_ketua_can_view_assigned_list(): void
    {
        $this->actingAs($this->ketuaAsesor)
            ->get(route('asesor.ketua.index'))
            ->assertStatus(200);
    }

    public function test_anggota_can_view_assigned_list(): void
    {
        $this->actingAs($this->anggotaAsesor)
            ->get(route('asesor.anggota.index'))
            ->assertStatus(200);
    }

    public function test_ketua_can_review_stage2(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);

        $this->actingAs($this->ketuaAsesor)
            ->get(route('asesor.ketua.review-tahap2', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }

    public function test_ketua_review_stage2_404_for_wrong_status(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_DRAFT_PROFILE);

        $this->actingAs($this->ketuaAsesor)
            ->get(route('asesor.ketua.review-tahap2', ['id' => $akreditasi->id]))
            ->assertStatus(404);
    }

    public function test_ketua_can_nyatakan_layak_visitasi(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);

        $this->actingAs($this->ketuaAsesor)
            ->post(route('asesor.ketua.nyatakan-layak-visitasi', ['id' => $akreditasi->id]))
            ->assertStatus(302);
    }

    public function test_ketua_can_jadwalkan_visitasi(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW);

        $this->actingAs($this->ketuaAsesor)
            ->post(route('asesor.ketua.jadwalkan-visitasi', ['id' => $akreditasi->id]), [
                'tgl_mulai' => '2026-07-01',
                'tgl_akhir' => '2026-07-03',
            ])
            ->assertStatus(302);
    }

    public function test_ketua_can_tandai_visitasi_selesai(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_VISITASI_SCHEDULED);

        $this->actingAs($this->ketuaAsesor)
            ->post(route('asesor.ketua.tandai-visitasi-selesai', ['id' => $akreditasi->id]))
            ->assertStatus(302);
    }

    public function test_anggota_can_view_input_na2_form(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_POST_VISITASI_SCORING);

        $this->actingAs($this->anggotaAsesor)
            ->get(route('asesor.anggota.input-na2', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }

    public function test_anggota_can_post_input_na2(): void
    {
        $akreditasi = $this->createAkreditasi(Akreditasi::STATUS_POST_VISITASI_SCORING);

        $this->actingAs($this->anggotaAsesor)
            ->post(route('asesor.anggota.input-na2', ['id' => $akreditasi->id]), [
                'butir_id' => [1, 2],
                'value' => [3, 4],
            ])
            ->assertStatus(302);
    }

    public function test_pesantren_user_is_not_an_asesor(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('asesor.ketua.index'))
            ->assertStatus(403);
    }
}
