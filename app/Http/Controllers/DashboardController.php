<?php

namespace App\Http\Controllers;

use App\Support\AppliedRows;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'appliedCount' => AppliedRows::count($user),
            'submissionCount' => $user->submissions()->count(),
            'recent' => AppliedRows::forUser($user, 5),
        ]);
    }
}
