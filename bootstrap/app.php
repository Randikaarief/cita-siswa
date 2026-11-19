<?php

use App\Http\Middleware\CaptureUtm;
use App\Http\Middleware\EnsureFreshPassword;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Catat kunjungan dari link kampanye SEBELUM pengalihan ke login.
        // Tanpa pengaturan urutan, middleware 'auth' berjalan lebih dulu dan tamu dari link Instagram tidak tercatat.
        $middleware->web(append: [CaptureUtm::class]);
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: CaptureUtm::class,
        );
        // Dua cookie ini hanya berisi penanda kampanye (bukan rahasia), jadi tidak perlu dienkripsi.
        $middleware->encryptCookies(except: [CaptureUtm::VISITOR_COOKIE, CaptureUtm::UTM_COOKIE]);

        // Semua halaman wajib login. Tamu diarahkan ke halaman login.
        $middleware->redirectGuestsTo(fn () => route('login'));
        // Yang sudah login dan membuka /login atau /register diarahkan ke halaman utama peran masing-masing.
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->homeUrl() ?? '/');

        $middleware->alias([
            'role' => EnsureRole::class,
            'password.fresh' => EnsureFreshPassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
