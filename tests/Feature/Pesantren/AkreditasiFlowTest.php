<?php

namespace Tests\Feature\Pesantren;

use App\Models\Akreditasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AkreditasiFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $pesantrenUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Test']);
    }

    public function test_can_view_akreditasi_index(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.index'))
            ->assertStatus(200);
    }

    public function test_can_view_pengajuan_form(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.pengajuan'))
            ->assertStatus(200);
    }

    public function test_submit_pengajuan_endpoint_exists(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->post(route('pesantren.akreditasi.submit-pengajuan'), [])
            ->assertStatus(302)
            ->assertSessionHas('error');
    }

    public function test_cannot_view_non_existent_assessment(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.assessment', ['id' => 999]))
            ->assertStatus(404);
    }

    public function test_cannot_access_hasil_for_non_existent(): void
    {
        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.hasil', ['id' => 999]))
            ->assertStatus(404);
    }

    public function test_can_view_hasil_for_completed_own_akreditasi(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
        ]);

        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.hasil', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }

    public function test_cannot_view_hasil_for_other_user_akreditasi(): void
    {
        $otherUser = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $otherUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_COMPLETED,
        ]);

        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.hasil', ['id' => $akreditasi->id]))
            ->assertStatus(404);
    }

    public function test_can_view_assessment_form_for_own_akreditasi(): void
    {
        $akreditasi = Akreditasi::create([
            'user_id' => $this->pesantrenUser->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_DRAFT_PROFILE,
        ]);

        $this->actingAs($this->pesantrenUser)
            ->get(route('pesantren.akreditasi.assessment', ['id' => $akreditasi->id]))
            ->assertStatus(200);
    }
}
