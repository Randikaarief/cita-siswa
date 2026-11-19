<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Data yang dipakai bersama oleh halaman dashboard siswa
        // (navbar avatar, badge jumlah alert di sidebar, dan sebagainya).
        View::composer(
            ['layouts.app', 'partials.account-nav', 'dashboard'],
            function ($view) {
                $user = Auth::user();

                $view->with([
                    'authUser' => $user,
                    'unreadAlerts' => $user ? $user->unreadNotifications()->count() : 0,
                ]);
            }
        );
    }
}
