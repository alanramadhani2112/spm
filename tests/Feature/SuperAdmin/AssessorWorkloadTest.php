<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\Assessment;
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
            ->assertSee('Pusat Workload Asesor')
            ->assertSee('Asesor Sibuk')
            ->assertSee('Aktif 5')
            ->assertSee('Overload')
            ->assertSee('Ketua 1')
            ->assertSee('Anggota 4')
            ->assertSee('Asesor Siap')
            ->assertSee('Aktif 1')
            ->assertSee('Assignment Aktif');
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

    public function test_non_super_admin_cannot_view_assessor_workload_center(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get(route('superadmin.asesor-workload.index'))
            ->assertForbidden();
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

    private function createAkreditasi(User $pesantren, string $status): Akreditasi
    {
        return Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
            'status_changed_at' => now()->subDay(),
        ]);
    }
}
