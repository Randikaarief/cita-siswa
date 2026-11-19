<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembatas akses per peran. Pemakaian di route: ->middleware('role:user')
 *  - akun dinonaktifkan: dikeluarkan
 *  - peran tidak cocok: diarahkan ke halaman utama perannya sendiri
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Akun ini dinonaktifkan.']);
        }

        if (! $user || ! in_array($user->role, $roles, true)) {
            return redirect($user ? $user->homeUrl() : '/login');
        }

        return $next($request);
    }
}
