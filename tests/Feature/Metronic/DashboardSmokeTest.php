<?php

namespace Tests\Feature\Metronic;

use App\Models\Akreditasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $ketuaAsesor;
    private User $anggotaAsesor;
    private User $pesantrenUser;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin Smoke', 'email' => 'admin-smoke@test.com']);
        $this->ketuaAsesor = User::factory()->create(['role_id' => 2, 'name' => 'Ketua Smoke', 'email' => 'ketua-smoke@test.com']);
        $this->anggotaAsesor = User::factory()->create(['role_id' => 2, 'name' => 'Anggota Smoke', 'email' => 'anggota-smoke@test.com']);
        $this->pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Smoke', 'email' => 'pesantren-smoke@test.com']);
        $this->superAdmin = User::factory()->create(['role_id' => 4, 'name' => 'Super Smoke', 'email' => 'super-smoke@test.com']);
    }

    public function test_metronic_dashboards_render_for_each_role(): void
    {
        $completed = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
        ]);

        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.index'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.akreditasi.index'))
            ->assertOk();

        $this->actingAs($this->ketuaAsesor)
            ->get(route('asesor.ketua.index'))
            ->assertOk();

        $this->actingAs($this->anggotaAsesor)
            ->get(route('asesor.anggota.index'))
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk();

        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.hasil', ['id' => $completed->id]))
            ->assertOk();
    }
}
