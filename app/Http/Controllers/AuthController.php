<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CaptureUtm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        // Dua pilihan masuk: siswa (juga dipakai mentor) dan admin.
        return view('auth.login', [
            'sebagai' => $request->query('sebagai') === 'admin' ? 'admin' : 'siswa',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password tidak cocok.']);
        }

        RateLimiter::clear($throttleKey);

        $user = $request->user();

        // Akun yang dinonaktifkan admin tidak boleh masuk.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun ini dinonaktifkan.']);
        }

        // Dari pilihan "Masuk sebagai Admin": hanya akun admin yang boleh lewat.
        if ($request->input('sebagai') === 'admin' && $user->role !== User::ROLE_ADMIN) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login', ['sebagai' => 'admin'])->withInput($request->only('email'))
                ->withErrors(['email' => 'Ini bukan akun admin. Silakan masuk lewat pilihan Siswa.']);
        }

        $request->session()->regenerate();

        // Kembali ke halaman yang tadi dituju (mis. link verifikasi email). Kalau tidak ada:
        // siswa ke Beranda, mentor ke /mentor, admin ke /admin.
        return redirect()->intended($user->homeUrl());
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User($data);
        $user->role = User::ROLE_USER; // pendaftaran lewat website selalu siswa

        // Simpan sumber kampanye (kalau datang dari link Instagram) untuk analisis corong.
        $utm = json_decode((string) $request->cookie(CaptureUtm::UTM_COOKIE), true);
        if (is_array($utm) && ! empty($utm['source'])) {
            $user->utm_source = CaptureUtm::clean($utm['source']);
            $user->utm_medium = CaptureUtm::clean($utm['medium'] ?? '');
            $user->utm_campaign = CaptureUtm::clean($utm['campaign'] ?? '');
        }
        $user->save();

        // Kirim email verifikasi. Kalau pengiriman gagal, pendaftaran tetap jadi dan siswa bisa kirim ulang.
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
