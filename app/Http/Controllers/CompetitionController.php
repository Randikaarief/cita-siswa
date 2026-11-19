<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $lokasi = (string) $request->query('lokasi', '');
        $kategori = (string) $request->query('kategori', '');

        $query = Competition::query()->orderBy('id');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%'.ScholarshipController::escapeLike($q).'%';
                $w->where('nama', 'like', $like)
                    ->orWhere('lokasi', 'like', $like)
                    ->orWhere('tipe', 'like', $like);
            });
        }
        if ($lokasi !== '') {
            $query->where('lokasi', 'like', '%'.ScholarshipController::escapeLike($lokasi).'%');
        }
        if ($kategori !== '') {
            $query->where('kategori', 'like', '%'.ScholarshipController::escapeLike($kategori).'%');
        }

        return view('lomba', [
            'competitions' => $query->get(),
            'q' => $q,
            'lokasi' => $lokasi,
            'kategori' => $kategori,
        ]);
    }

    public function show(int $id): View
    {
        return view('lomba-detail', [
            'a' => Competition::findOrFail($id),
            'mentors' => MentorProfile::available()->orderBy('nama')->get(['id', 'nama', 'is_sample']),
            'mySubmissions' => auth()->user()->submissions()->latest()->get(),
        ]);
    }
}
