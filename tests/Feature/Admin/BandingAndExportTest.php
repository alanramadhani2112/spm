<?php

namespace Tests\Feature\Admin;

use App\Models\Akreditasi;
use App\Models\Banding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BandingAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_banding_review_page(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_APPEAL_SUBMITTED,
        ]);
        Banding::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $pesantren->id,
            'reason' => 'Mohon peninjauan ulang.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.akreditasi.banding', $akreditasi->id))
            ->assertOk()
            ->assertSee('Review Permohonan Banding')
            ->assertSee('Mohon peninjauan ulang.');
    }

    public function test_admin_export_returns_csv(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $pesantren = User::factory()->create(['role_id' => 3]);
        Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.akreditasi.export'))
            ->assertOk()
            ->assertDownload('akreditasi-admin.csv');
    }
}
