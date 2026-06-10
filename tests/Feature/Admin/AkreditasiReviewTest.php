<?php

namespace Tests\Feature\Admin;

use App\Models\Akreditasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AkreditasiReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pesantrenUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin Test', 'email' => 'admin@test.com']);
        $this->pesantrenUser = User::factory()->create(['role_id' => 3]);
    }

    public function test_admin_can_view_akreditasi_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.akreditasi.index'))
            ->assertStatus(200);
    }

    public function test_admin_can_review_initial_submitted(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.akreditasi.review-awal', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }

    public function test_review_awal_404_for_wrong_status(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_DRAFT_PROFILE,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.akreditasi.review-awal', ['id' => $akreditasi->id]))
            ->assertStatus(404);
    }

    public function test_admin_can_accept_pengajuan(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.akreditasi.terima-pengajuan', ['id' => $akreditasi->id]))
            ->assertStatus(302);

        $this->assertDatabaseHas('akreditasis', [
            'id' => $akreditasi->id,
            'status' => Akreditasi::STATUS_ASSESSMENT_OPEN,
        ]);
    }

    public function test_admin_can_reject_pengajuan(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.akreditasi.tolak-pengajuan', ['id' => $akreditasi->id]), [
                'reason' => 'Profil tidak lengkap',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('akreditasis', [
            'id' => $akreditasi->id,
            'status' => Akreditasi::STATUS_INITIAL_REJECTED,
        ]);
    }

    public function test_reject_pengajuan_requires_reason(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.akreditasi.tolak-pengajuan', ['id' => $akreditasi->id]), [])
            ->assertStatus(302)
            ->assertSessionHasErrors('reason');
    }

    public function test_admin_can_view_review_tahap1(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.akreditasi.review-tahap1', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }

    public function test_admin_can_approve_stage1(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.akreditasi.approve-tahap1', ['id' => $akreditasi->id]))
            ->assertStatus(302);
    }

    public function test_pesantren_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('admin.akreditasi.index'))
            ->assertStatus(403);
    }
}
