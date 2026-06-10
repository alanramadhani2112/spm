<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
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
            ->assertSee('Dashboard Super Admin');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard.export'))
            ->assertOk()
            ->assertDownload('dashboard-superadmin.csv');
    }
}
