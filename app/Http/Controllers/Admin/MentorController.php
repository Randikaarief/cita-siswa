<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mentor\ProfileController as MentorProfileController;
use App\Http\Controllers\ScholarshipController as PublicScholarshipController;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Kelola akun dan profil mentor. Akun mentor hanya dibuat admin. */
class MentorController extends Controller
{
    public const KATEGORI = ['Pemerintah', 'Private Company', 'Lembaga Internasional'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $items = MentorProfile::withoutGlobalScopes()
            ->with(['user' => fn ($w) => $w->select('id', 'name', 'email', 'is_active', 'must_change_password')])
            ->when($q !== '', fn ($w) => $w->where('nama', 'like', '%'.PublicScholarshipController::escapeLike($q).'%'))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.mentors.index', ['items' => $items, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.mentors.create', ['kategori' => self::KATEGORI]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'beasiswa' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'in:'.implode(',', self::KATEGORI)],
        ]);

        DB::transaction(function () use ($data) {
            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->role = User::ROLE_MENTOR;
            $user->must_change_password = true; // wajib ganti password saat login pertama
            $user->save();

            MentorProfile::withoutGlobalScopes()->create([
                'user_id' => $user->id,
                'nama' => $data['name'],
                'lokasi' => $data['lokasi'] ?? null,
                'beasiswa' => $data['beasiswa'] ?? null,
                'kategori' => $data['kategori'] ?? null,
            ]);
        });

        return redirect()->route('admin.mentors.index')
            ->with('status', 'Akun mentor dibuat. Berikan password sementara ke mentor; ia wajib menggantinya saat login pertama.');
    }

    public function edit(int $mentor): View
    {
        return view('admin.mentors.edit', [
            'item' => MentorProfile::withoutGlobalScopes()->with('user')->findOrFail($mentor),
            'kategori' => self::KATEGORI,
        ]);
    }

    public function update(Request $request, int $mentor): RedirectResponse
    {
        $item = MentorProfile::withoutGlobalScopes()->findOrFail($mentor);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'beasiswa' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'in:'.implode(',', self::KATEGORI)],
            'description' => ['nullable', 'string', 'max:3000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{7,18}$/'],
            'max_active_requests' => ['nullable', 'integer', 'min:0', 'max:50'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
        ]);

        $data['youtube'] = MentorProfileController::youtubeId($data['youtube'] ?? null);
        $data['is_willing_to_help'] = $request->boolean('is_willing_to_help');
        $data['is_visible'] = $request->boolean('is_visible');
        $data['is_sample'] = $request->boolean('is_sample');

        $item->update($data);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    /** Nonaktifkan atau aktifkan kembali mentor (akun dan tampilan profil). */
    public function toggleActive(int $mentor): RedirectResponse
    {
        $item = MentorProfile::withoutGlobalScopes()->with('user')->findOrFail($mentor);

        $activate = $item->user ? ! $item->user->is_active : ! $item->is_visible;

        if ($item->user) {
            // is_active sengaja tidak mass-assignable, jadi diisi langsung.
            $item->user->is_active = $activate;
            $item->user->save();
        }

        $item->update($activate
            ? ['is_visible' => true]
            : ['is_visible' => false, 'is_willing_to_help' => false]);

        return back()->with('status', $activate ? 'Mentor diaktifkan kembali.' : 'Mentor dinonaktifkan.');
    }

    public function resetPassword(Request $request, int $mentor): RedirectResponse
    {
        $item = MentorProfile::withoutGlobalScopes()->with('user')->findOrFail($mentor);
        abort_unless($item->user, 404);

        $data = $request->validate(['password' => ['required', Password::min(8)]]);

        $item->user->password = $data['password'];
        $item->user->must_change_password = true;
        $item->user->setRememberToken(null);
        $item->user->save();

        return back()->with('status', 'Password sementara diatur. Mentor wajib menggantinya saat login berikutnya.');
    }
}
