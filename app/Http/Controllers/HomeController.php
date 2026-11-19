<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\Scholarship;
use App\Http\Middleware\EnsureRole;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Alamat "/":
     *  - tamu (belum login): halaman sambutan dengan ajakan masuk/daftar
     *  - siswa: Beranda
     *  - mentor/admin: diarahkan ke halaman utama perannya (aturan sama seperti role:user)
     * Pencatatan UTM tetap jalan karena middleware CaptureUtm berjalan di semua permintaan web.
     */
    public function index(Request $request): mixed
    {
        if (! $request->user()) {
            return view('landing');
        }

        return app(EnsureRole::class)->handle(
            $request,
            fn () => new Response($this->student()),
            'user',
        );
    }

    private function student(): View
    {
        return view('welcome', [
            'scholarships' => Scholarship::orderByDesc('id')->limit(6)->get(),
            'competitions' => Competition::orderByDesc('id')->limit(3)->get(),
            // Nomor WhatsApp tidak pernah diambil untuk halaman umum.
            'mentors' => MentorProfile::select(['id', 'nama', 'tipe', 'lokasi', 'beasiswa', 'kategori', 'is_sample'])
                ->orderByDesc('id')->limit(3)->get(),
        ]);
    }
}
