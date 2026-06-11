<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\MuhammadiyahIdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MuhammadiyahIdController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function redirect(MuhammadiyahIdService $muhammadiyahId): RedirectResponse
    {
        try {
            $state = Str::random(48);
            session(['muhammadiyah_id_oauth_state' => $state]);

            return redirect()->away($muhammadiyahId->authorizationUrl($state));
        } catch (Throwable $exception) {
            Log::warning('Muhammadiyah ID redirect failed.', ['message' => $exception->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Login Muhammadiyah ID belum siap. Periksa konfigurasi SSO terlebih dahulu.',
            ]);
        }
    }

    public function callback(Request $request, MuhammadiyahIdService $muhammadiyahId): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Muhammadiyah ID dibatalkan atau ditolak.',
            ]);
        }

        $state = session()->pull('muhammadiyah_id_oauth_state');

        if (blank($state) || ! hash_equals((string) $state, (string) $request->query('state'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi login Muhammadiyah ID tidak valid. Silakan coba lagi.',
            ]);
        }

        if (! $request->filled('code')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Kode OAuth Muhammadiyah ID tidak ditemukan.',
            ]);
        }

        try {
            $token = $muhammadiyahId->exchangeCodeForToken((string) $request->query('code'));
            $profile = $muhammadiyahId->normalizeProfile(
                $muhammadiyahId->getUserInfo((string) $token['access_token'])
            );
            $user = $this->findOrCreateLocalUser($profile);
            $wasLinkedToSso = filled($user->sso_id);

            if (($user->status ?? 'active') !== 'active') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda sedang nonaktif. Hubungi Super Admin untuk mengaktifkan akses.',
                ]);
            }

            $user->forceFill([
                'name' => $profile['name'] ?: $user->name,
                'sso_id' => $profile['sso_id'],
                'm_id' => $profile['m_id'],
                'nbm' => $profile['nbm'],
                'phone' => $profile['phone'],
                'avatar_url' => $profile['avatar_url'],
                'sso_level' => $profile['sso_level'],
                'sso_role' => $profile['sso_role'],
                'sso_groups' => $profile['sso_groups'],
                'last_sso_login_at' => now(),
            ])->save();

            if (! $wasLinkedToSso) {
                $this->auditTrail->log('sso_user_linked', null, $user->id, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'sso_id' => $profile['sso_id'],
                    'm_id' => $profile['m_id'],
                    'nbm' => $profile['nbm'],
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return $this->redirectAfterLogin($user);
        } catch (Throwable $exception) {
            Log::warning('Muhammadiyah ID callback failed.', ['message' => $exception->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Login Muhammadiyah ID gagal. Pastikan akun sudah diundang dan coba lagi.',
            ]);
        }
    }

    private function findOrCreateLocalUser(array $profile): User
    {
        $user = User::query()
            ->when(filled($profile['sso_id']), fn ($query) => $query->orWhere('sso_id', $profile['sso_id']))
            ->when(filled($profile['m_id']), fn ($query) => $query->orWhere('m_id', $profile['m_id']))
            ->when(filled($profile['nbm']), fn ($query) => $query->orWhere('nbm', $profile['nbm']))
            ->when(filled($profile['email']), fn ($query) => $query->orWhere('email', $profile['email']))
            ->first();

        if ($user) {
            return $user;
        }

        if ((bool) config('services.muhammadiyah_id.require_pre_registered', true)) {
            throw new \RuntimeException('User Muhammadiyah ID belum terdaftar di PesantrenMu.');
        }

        $pesantrenRole = Role::where('parameter', 'pesantren')->firstOrFail();

        return User::forceCreate([
            'name' => $profile['name'] ?: 'Pengguna Muhammadiyah ID',
            'email' => $profile['email'],
            'password' => Hash::make(Str::random(64)),
            'role_id' => $pesantrenRole->id,
            'uuid' => (string) Str::uuid(),
            'status' => 'active',
        ]);
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        return match ($user->role?->parameter) {
            'super_admin', 'superadmin' => redirect()->intended(route('superadmin.dashboard')),
            'admin' => redirect()->intended(route('admin.akreditasi.index')),
            'asesor' => redirect()->intended(route('asesor.ketua.index')),
            'pesantren' => redirect()->intended(route('pesantren.akreditasi.index')),
            default => redirect()->intended('/'),
        };
    }
}
