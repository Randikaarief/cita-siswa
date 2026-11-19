<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    private const SOCIAL_KEYS = ['facebook', 'twitter', 'instagram', 'youtube'];

    // ---------- Personal ----------
    public function personal(Request $request): View
    {
        return view('settings-personal', [
            'user' => $request->user(),
            'submissions' => $request->user()->submissions()->latest()->get(),
        ]);
    }

    public function updatePersonal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $user = $request->user();

        if ($user->photo_path) {
            Storage::disk('local')->delete($user->photo_path);
        }

        $user->photo_path = $request->file('photo')->store('avatars/'.$user->id, 'local');
        $user->save();

        return back()->with('status', 'Foto tersimpan.');
    }

    /** Foto profil hanya bisa dilihat pemiliknya. */
    public function photo(Request $request): StreamedResponse
    {
        $path = $request->user()->photo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    // ---------- Profile ----------
    public function profile(Request $request): View
    {
        return view('settings-profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nationality' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'gender' => ['nullable', 'string', 'max:20'],
            'education_level' => ['nullable', 'string', 'max:50'],
        ], [
            'birth_date.date_format' => 'Format tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        $data['birth_date'] = filled($data['birth_date'] ?? null)
            ? Carbon::createFromFormat('Y-m-d', $data['birth_date'])->startOfDay()
            : null;

        $request->user()->update($data);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    // ---------- Social links ----------
    public function social(Request $request): View
    {
        return view('settings-social', ['user' => $request->user()]);
    }

    public function updateSocial(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::SOCIAL_KEYS as $key) {
            $rules[$key] = ['nullable', 'string', 'max:255'];
        }
        $data = $request->validate($rules);

        $request->user()->update([
            'social_links' => collect(self::SOCIAL_KEYS)
                ->mapWithKeys(fn ($key) => [$key => $data[$key] ?? null])
                ->all(),
        ]);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    // ---------- Account ----------
    public function account(Request $request): View
    {
        return view('settings-account', ['user' => $request->user()]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $emailChanged = $data['email'] !== $user->email;

        $user->update($data);

        // Email baru harus diverifikasi lagi.
        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }

            return back()->with('status', 'Perubahan tersimpan. Kami kirim email verifikasi ke alamat barumu.');
        }

        return back()->with('status', 'Perubahan tersimpan.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Password diganti.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Password tidak cocok.',
        ]);

        $user = $request->user();

        // Hapus file privat milik siswa ini.
        Storage::disk('local')->deleteDirectory('submissions/'.$user->id);
        Storage::disk('local')->deleteDirectory('avatars/'.$user->id);

        Auth::logout();
        $user->delete(); // pengajuan, riwayat klik, dan berkas ikut terhapus (cascade)

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
