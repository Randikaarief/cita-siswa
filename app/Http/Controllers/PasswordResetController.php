<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/** Lupa password: kirim link reset ke email, lalu atur password baru. */
class PasswordResetController extends Controller
{
    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Isi emailmu dulu.',
            'email.email' => 'Format email belum benar.',
        ]);

        try {
            Password::sendResetLink(['email' => $data['email']]);
        } catch (\Throwable $e) {
            report($e);
        }

        // Jawaban selalu sama, supaya orang lain tidak bisa menebak email siapa yang terdaftar.
        return back()->with('status', 'Kalau emailnya terdaftar, link untuk mengatur ulang password sudah kami kirim. Cek juga folder spam.');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.confirmed' => 'Ketik ulang password harus sama.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.required' => 'Isi password barumu.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                // Yang bisa membuka link ini pasti memegang emailnya, jadi emailnya sekaligus terverifikasi.
                if ($user->isStudent() && ! $user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                // Password baru dipilih sendiri, jadi tidak perlu dipaksa ganti lagi.
                $user->must_change_password = false;
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Link reset tidak valid atau sudah kedaluwarsa. Minta link baru ya.']);
        }

        return redirect()->route('login')->with('status', 'Password baru tersimpan. Silakan masuk.');
    }
}
