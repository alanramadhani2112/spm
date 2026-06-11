<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MuhammadiyahIdSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.muhammadiyah_id.client_id', 'client-id-test');
        config()->set('services.muhammadiyah_id.client_secret', 'client-secret-test');
        config()->set('services.muhammadiyah_id.redirect_uri', 'http://localhost/auth/muhammadiyah/callback');
        config()->set('services.muhammadiyah_id.base_url', 'https://sso.muhammadiyah.id');
        config()->set('services.muhammadiyah_id.api_url', 'https://sso.muhammadiyah.id/api');
        config()->set('services.muhammadiyah_id.scope', 'user-info');
        config()->set('services.muhammadiyah_id.require_pre_registered', true);
    }

    public function test_login_page_shows_muhammadiyah_id_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Masuk dengan Muhammadiyah ID')
            ->assertSee(route('auth.muhammadiyah.redirect'));
    }

    public function test_redirect_starts_oauth_flow_with_state(): void
    {
        $response = $this->get(route('auth.muhammadiyah.redirect'));

        $response->assertRedirectContains('https://sso.muhammadiyah.id/oauth/authorize');
        $response->assertRedirectContains('response_type=code');
        $response->assertRedirectContains('client_id=client-id-test');
        $response->assertRedirectContains('scope=user-info');
        $response->assertSessionHas('muhammadiyah_id_oauth_state');
    }

    public function test_callback_links_pre_registered_user_and_logs_in(): void
    {
        $user = User::factory()->create([
            'role_id' => 4,
            'email' => 'fulan@example.com',
            'm_id' => '0000 0000 0000 0000',
            'status' => 'active',
        ]);

        Http::fake([
            'https://sso.muhammadiyah.id/oauth/token' => Http::response([
                'access_token' => 'access-token-test',
                'refresh_token' => 'refresh-token-test',
                'expires_in' => 3600,
            ]),
            'https://sso.muhammadiyah.id/api/user-info' => Http::response([
                'sso_id' => 'abcde123456efg',
                'm_id' => '0000 0000 0000 0000',
                'nbm' => '123456',
                'name' => 'Fulan',
                'level' => '1',
                'image' => 'https://example.com/avatar.jpg',
                'email' => 'fulan@example.com',
                'phone' => '628123456789',
                'role' => 'user',
                'group' => [
                    ['group_id' => 1, 'group_name' => 'Group 1'],
                ],
            ]),
        ]);

        $this->withSession(['muhammadiyah_id_oauth_state' => 'valid-state'])
            ->get(route('auth.muhammadiyah.callback', [
                'code' => 'oauth-code-test',
                'state' => 'valid-state',
            ]))
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'sso_id' => 'abcde123456efg',
            'nbm' => '123456',
            'phone' => '628123456789',
            'sso_role' => 'user',
        ]);
        $this->assertNotNull($user->fresh()->last_sso_login_at);
    }

    public function test_callback_rejects_invalid_state(): void
    {
        $this->withSession(['muhammadiyah_id_oauth_state' => 'valid-state'])
            ->get(route('auth.muhammadiyah.callback', [
                'code' => 'oauth-code-test',
                'state' => 'invalid-state',
            ]))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
