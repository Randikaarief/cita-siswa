<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Verifikasi email siswa: halaman "cek emailmu", link verifikasi, dan kirim ulang. */
class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect('/')->with('status', 'Email terverifikasi. Sekarang kamu bisa mengunggah berkas dan minta masukan mentor.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Email belum bisa dikirim. Coba lagi nanti, atau hubungi admin.']);
        }

        return back()->with('status', 'Email verifikasi sudah dikirim ulang. Cek juga folder spam.');
    }
}
