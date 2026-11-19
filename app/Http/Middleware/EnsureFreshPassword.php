<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mentor/admin dengan password sementara dipaksa ganti password dulu
 * sebelum membuka halaman lain.
 */
class EnsureFreshPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('panel.password*')) {
            return redirect()->route('panel.password')
                ->with('status', 'Ganti password sementara dulu sebelum lanjut.');
        }

        return $next($request);
    }
}
