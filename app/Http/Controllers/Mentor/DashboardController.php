<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ReviewRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->mentorProfile;

        $base = ReviewRequest::query()->where('mentor_profile_id', $profile?->id ?? 0);

        return view('mentor.dashboard', [
            'profile' => $profile,
            'pending' => (clone $base)->open()->count(),
            'replied' => (clone $base)->where('status', ReviewRequest::REPLIED)->count(),
            'declined' => (clone $base)->where('status', ReviewRequest::DECLINED)->count(),
            'recent' => $profile
                ? RequestController::query($profile->id)->open()->latest()->limit(5)->get()
                : collect(),
        ]);
    }
}
