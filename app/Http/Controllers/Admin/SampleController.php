<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\Scholarship;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/** Sembunyikan atau hapus semua data contoh (is_sample) sebelum peluncuran. */
class SampleController extends Controller
{
    public function index(): View
    {
        $count = fn (string $model) => $model::withoutGlobalScopes()->where('is_sample', true)->count();

        return view('admin.samples', [
            'counts' => [
                'Beasiswa' => $count(Scholarship::class),
                'Lomba' => $count(Competition::class),
                'Mentor' => $count(MentorProfile::class),
                'Testimoni' => $count(Testimonial::class),
            ],
        ]);
    }

    public function hide(): RedirectResponse
    {
        Artisan::call('samples:purge', ['--hide' => true]);

        return back()->with('status', 'Data contoh disembunyikan dari siswa.');
    }

    public function purge(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['required', 'in:HAPUS']], [
            'confirm.in' => 'Ketik HAPUS (huruf besar) untuk memastikan.',
            'confirm.required' => 'Ketik HAPUS (huruf besar) untuk memastikan.',
        ]);

        Artisan::call('samples:purge');

        return back()->with('status', 'Data contoh dihapus permanen.');
    }
}
