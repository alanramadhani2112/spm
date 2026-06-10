<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $user?->role?->parameter;

        if (! $role && $user?->role_id) {
            $role = [
                1 => 'admin',
                2 => 'asesor',
                3 => 'pesantren',
                4 => 'super_admin',
            ][(int) $user->role_id] ?? null;
        }

        if ($role === 'superadmin') {
            $role = 'super_admin';
        }

        if (! $role || ! in_array($role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
