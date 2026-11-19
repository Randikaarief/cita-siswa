<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $lokasi = (string) $request->query('lokasi', '');
        $kategori = (string) $request->query('kategori', '');

        $query = Scholarship::query()->orderBy('id');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%'.self::escapeLike($q).'%';
                $w->where('nama', 'like', $like)
                    ->orWhere('sub', 'like', $like)
                    ->orWhere('lokasi', 'like', $like);
            });
        }
        if ($lokasi !== '') {
            $query->where('lokasi', 'like', '%'.self::escapeLike($lokasi).'%');
        }
        if ($kategori !== '') {
            $query->where('kategori', 'like', '%'.self::escapeLike($kategori).'%');
        }

        return view('find-scholarship', [
            'scholarships' => $query->get(),
            'q' => $q,
            'lokasi' => $lokasi,
            'kategori' => $kategori,
        ]);
    }

    public function show(int $id): View
    {
        return view('find-scholarship-detail', [
            's' => Scholarship::findOrFail($id), // ID tidak ada = 404
            'mentors' => MentorProfile::available()->orderBy('nama')->get(['id', 'nama', 'is_sample']),
            'mySubmissions' => auth()->user()->submissions()->latest()->get(),
        ]);
    }

    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
