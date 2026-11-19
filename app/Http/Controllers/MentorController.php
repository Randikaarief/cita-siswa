<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman publik "Alumni" (alumni = mentor). Nomor WhatsApp tidak pernah dikirim ke view. */
class MentorController extends Controller
{
    private const COLUMNS = [
        'id', 'nama', 'tipe', 'lokasi', 'mode', 'beasiswa', 'kategori', 'founded', 'biaya',
        'description', 'benefits', 'avatar_color', 'youtube', 'is_sample', 'is_willing_to_help',
    ];

    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $lokasi = (string) $request->query('lokasi', '');
        $kategori = (string) $request->query('kategori', '');

        $query = MentorProfile::query()->select(self::COLUMNS)->orderBy('id');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%'.ScholarshipController::escapeLike($q).'%';
                $w->where('nama', 'like', $like)
                    ->orWhere('lokasi', 'like', $like)
                    ->orWhere('beasiswa', 'like', $like)
                    ->orWhere('tipe', 'like', $like);
            });
        }
        if ($lokasi !== '') {
            $query->where('lokasi', 'like', '%'.ScholarshipController::escapeLike($lokasi).'%');
        }
        if ($kategori !== '') {
            $query->where('kategori', 'like', '%'.ScholarshipController::escapeLike($kategori).'%');
        }

        return view('alumni', [
            'alumni' => $query->get(),
            'q' => $q,
            'lokasi' => $lokasi,
            'kategori' => $kategori,
        ]);
    }

    public function show(int $id): View
    {
        $mentor = MentorProfile::select(self::COLUMNS)->findOrFail($id);

        return view('detail', [
            'a' => $mentor,
            'testimonials' => \App\Models\Testimonial::where('mentor_profile_id', $mentor->id)->latest('id')->get(),
        ]);
    }
}
