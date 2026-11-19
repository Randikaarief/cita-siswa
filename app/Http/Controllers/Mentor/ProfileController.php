<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Support\Url;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('mentor.profile', ['profile' => $this->profile($request)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'beasiswa' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{7,18}$/'],
            'max_active_requests' => ['nullable', 'integer', 'min:0', 'max:50'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
        ]);

        $data['youtube'] = self::youtubeId($data['youtube'] ?? null);
        $data['is_willing_to_help'] = $request->boolean('is_willing_to_help');

        $this->profile($request)->update($data);

        return back()->with('status', 'Profil tersimpan.');
    }

    private function profile(Request $request): MentorProfile
    {
        $user = $request->user();

        return $user->mentorProfile
            ?: MentorProfile::withoutGlobalScopes()->create(['user_id' => $user->id, 'nama' => $user->name]);
    }

    /** Terima ID video atau link YouTube lengkap, simpan ID-nya saja. */
    public static function youtubeId(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#(?:youtu\.be/|[?&]v=|/embed/|/shorts/)([A-Za-z0-9_-]{6,20})#', $value, $m)) {
            return $m[1];
        }

        return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value) ? $value : null;
    }
}
