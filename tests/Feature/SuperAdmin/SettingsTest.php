<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role_id' => 4, 'name' => 'Super Admin', 'email' => 'super@test.com']);
        $this->admin = User::factory()->create(['role_id' => 1, 'email' => 'admin2@test.com']);
    }

    public function test_super_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertStatus(200);
    }

    public function test_super_admin_can_view_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.settings.index'))
            ->assertStatus(200);
    }

    public function test_super_admin_can_view_audit_logs(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.audit.index'))
            ->assertStatus(200);
    }

    public function test_admin_cannot_access_super_admin_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('superadmin.dashboard'))
            ->assertStatus(403);
    }

    public function test_update_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.settings.update'), [
                'key' => 'review_awal_deadline',
                'value' => '7',
                'reason' => 'testing update',
            ])
            ->assertStatus(302);
    }

    public function test_settings_index_loads_with_nav_tabs(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.settings.deadline'))
            ->assertStatus(200);
    }
}
