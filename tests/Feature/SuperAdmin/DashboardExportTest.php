<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_export_returns_csv(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $pesantren = User::factory()->create(['role_id' => 3]);
        Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Dashboard Super Admin')
            ->assertSee('Ringkasan Nasional Akreditasi')
            ->assertSee('Apa yang perlu dipantau hari ini?')
            ->assertSee('Prioritas', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard.export'))
            ->assertOk()
            ->assertDownload('dashboard-superadmin.csv');
    }

    public function test_super_admin_dashboard_shows_operational_board(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Board']);
        $asesor = User::factory()->create(['role_id' => 2, 'name' => 'Asesor Workload']);

        $reviewAwal = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
            'status_changed_at' => now()->subDays(8),
        ]);

        $assignment = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            'status_changed_at' => now()->subDays(2),
        ]);

        Assessment::create([
            'akreditasi_id' => $assignment->id,
            'asesor_id' => $asesor->id,
            'tipe' => 'ketua',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Operational Board')
            ->assertSee('Antrian God Mode')
            ->assertSee('SLA Breach')
            ->assertSee('Antrian Paling Mendesak')
            ->assertSee('Workload Asesor')
            ->assertSee('Review Awal')
            ->assertSee('Asesor Workload')
            ->assertSee($reviewAwal->uuid);
    }
}
