<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->mentorProfile;

        return view('mentor.testimonials', [
            'profile' => $profile,
            'testimonials' => $profile
                ? Testimonial::withoutGlobalScopes()->where('mentor_profile_id', $profile->id)->latest()->get()
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->mentorProfile;
        abort_unless($profile, 404);

        $data = $request->validate([
            'youtube' => ['nullable', 'string', 'max:255'],
            'quote' => ['nullable', 'string', 'max:1000'],
        ]);

        $youtube = ProfileController::youtubeId($data['youtube'] ?? null);

        if (! $youtube && blank($data['quote'] ?? null)) {
            return back()->withErrors(['quote' => 'Isi teks testimoni atau link video YouTube yang valid.'])->withInput();
        }

        Testimonial::create([
            'mentor_profile_id' => $profile->id,
            'youtube' => $youtube,
            'quote' => $data['quote'] ?? null,
        ]);

        return back()->with('status', 'Testimoni ditambahkan.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $profile = $request->user()->mentorProfile;
        $testimonial = Testimonial::withoutGlobalScopes()->findOrFail($id);
        abort_unless($profile && $testimonial->mentor_profile_id === $profile->id, 404);

        $testimonial->delete();

        return back()->with('status', 'Testimoni dihapus.');
    }
}
