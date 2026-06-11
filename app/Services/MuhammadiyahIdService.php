<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MuhammadiyahIdService
{
    public function authorizationUrl(string $state): string
    {
        $this->ensureConfigured();

        return config('services.muhammadiyah_id.base_url').'/oauth/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.muhammadiyah_id.client_id'),
            'redirect_uri' => config('services.muhammadiyah_id.redirect_uri'),
            'state' => $state,
            'scope' => config('services.muhammadiyah_id.scope', 'user-info'),
        ]);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $this->ensureConfigured();

        $response = $this->http()
            ->asForm()
            ->post(config('services.muhammadiyah_id.base_url').'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.muhammadiyah_id.client_id'),
                'client_secret' => config('services.muhammadiyah_id.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.muhammadiyah_id.redirect_uri'),
            ])
            ->throw()
            ->json();

        if (! is_array($response) || blank($response['access_token'] ?? null)) {
            throw new RuntimeException('Response token Muhammadiyah ID tidak valid.');
        }

        return $response;
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = $this->http()
            ->withToken($accessToken)
            ->get(config('services.muhammadiyah_id.api_url').'/user-info')
            ->throw()
            ->json();

        if (! is_array($response) || blank($response['email'] ?? null)) {
            throw new RuntimeException('Response user-info Muhammadiyah ID tidak valid.');
        }

        return $response;
    }

    public function normalizeProfile(array $profile): array
    {
        return [
            'sso_id' => $profile['sso_id'] ?? null,
            'm_id' => $profile['m_id'] ?? null,
            'nbm' => $profile['nbm'] ?? null,
            'name' => $profile['name'] ?? null,
            'email' => $profile['email'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'avatar_url' => $profile['image'] ?? null,
            'sso_level' => $profile['level'] ?? null,
            'sso_role' => $profile['role'] ?? null,
            'sso_groups' => $profile['group'] ?? [],
        ];
    }

    private function ensureConfigured(): void
    {
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            if (blank(config("services.muhammadiyah_id.{$key}"))) {
                throw new RuntimeException("Konfigurasi Muhammadiyah ID belum lengkap: {$key}.");
            }
        }
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(15)
            ->retry(2, 200)
            ->withUserAgent('PesantrenMu SSO/'.Str::slug(config('app.name', 'app')));
    }
}
