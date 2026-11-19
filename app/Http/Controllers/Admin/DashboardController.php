<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'students' => User::where('role', User::ROLE_USER)->count(),
            'mentors' => MentorProfile::withoutGlobalScopes()->where('is_sample', false)->count(),
            'scholarships' => Scholarship::withoutGlobalScopes()->where('is_sample', false)->count(),
            'competitions' => Competition::withoutGlobalScopes()->where('is_sample', false)->count(),
            'pending' => ReviewRequest::where('status', ReviewRequest::PENDING)->count(),
            'samples' => Scholarship::withoutGlobalScopes()->where('is_sample', true)->count()
                + Competition::withoutGlobalScopes()->where('is_sample', true)->count()
                + MentorProfile::withoutGlobalScopes()->where('is_sample', true)->count(),
        ]);
    }
}
