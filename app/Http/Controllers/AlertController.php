<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $newCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->limit(50)->get();

        // Halaman dibuka = semua dianggap sudah dibaca.
        $user->unreadNotifications->markAsRead();

        return view('alert', [
            'notifications' => $notifications,
            'newCount' => $newCount,
        ]);
    }
}
