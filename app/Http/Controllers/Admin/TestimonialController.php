<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mentor\ProfileController as MentorProfileController;
use App\Models\MentorProfile;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials', [
            'items' => Testimonial::withoutGlobalScopes()->with(['mentorProfile' => fn ($w) => $w->withoutGlobalScopes()->select('id', 'nama')])->latest()->paginate(20),
            'mentors' => MentorProfile::withoutGlobalScopes()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mentor_profile_id' => ['required', 'integer', 'exists:mentor_profiles,id'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'quote' => ['nullable', 'string', 'max:1000'],
        ]);

        $youtube = MentorProfileController::youtubeId($data['youtube'] ?? null);
        if (! $youtube && blank($data['quote'] ?? null)) {
            return back()->withErrors(['quote' => 'Isi teks testimoni atau link video YouTube yang valid.'])->withInput();
        }

        Testimonial::create([
            'mentor_profile_id' => $data['mentor_profile_id'],
            'youtube' => $youtube,
            'quote' => $data['quote'] ?? null,
        ]);

        return back()->with('status', 'Testimoni ditambahkan.');
    }

    public function toggle(int $id): RedirectResponse
    {
        $t = Testimonial::withoutGlobalScopes()->findOrFail($id);
        $t->update(['is_visible' => ! $t->is_visible]);

        return back()->with('status', $t->is_visible ? 'Testimoni ditampilkan.' : 'Testimoni disembunyikan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Testimonial::withoutGlobalScopes()->findOrFail($id)->delete();

        return back()->with('status', 'Testimoni dihapus.');
    }
}
