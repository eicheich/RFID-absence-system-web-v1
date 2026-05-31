<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasRole($role)) {
            // Redirect only to a valid owned dashboard to avoid redirect loops.
            if ($user->hasRole('hrd')) {
                return redirect()->route('hrd.dashboard');
            }

            if ($user->hasRole('karyawan')) {
                return redirect()->route('karyawan.dashboard');
            }

            abort(403, 'Akun tidak memiliki role yang diizinkan.');
        }

        return $next($request);
    }
}
