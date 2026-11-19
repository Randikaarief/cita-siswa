<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarshipController as PublicScholarshipController;
use App\Models\Competition;
use App\Support\Url;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $items = Competition::withoutGlobalScopes()
            ->when($q !== '', fn ($w) => $w->where('nama', 'like', '%'.PublicScholarshipController::escapeLike($q).'%'))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.competitions.index', ['items' => $items, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.competitions.form', ['item' => new Competition(['is_visible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Competition::create($this->validated($request));

        return redirect()->route('admin.competitions.index')->with('status', 'Lomba ditambahkan.');
    }

    public function edit(int $competition): View
    {
        return view('admin.competitions.form', ['item' => Competition::withoutGlobalScopes()->findOrFail($competition)]);
    }

    public function update(Request $request, int $competition): RedirectResponse
    {
        Competition::withoutGlobalScopes()->findOrFail($competition)->update($this->validated($request));

        return redirect()->route('admin.competitions.index')->with('status', 'Perubahan tersimpan.');
    }

    public function destroy(int $competition): RedirectResponse
    {
        Competition::withoutGlobalScopes()->findOrFail($competition)->delete();

        return redirect()->route('admin.competitions.index')->with('status', 'Lomba dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'string', 'max:20'],
            'beasiswa' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'founded' => ['nullable', 'string', 'max:255'],
            'biaya' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) {
                if (filled($value) && Url::official($value) === null) {
                    $fail('Link resmi harus berupa alamat web (http atau https).');
                }
            }],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'benefits' => ['nullable', 'string', 'max:5000'],
            'avatar_color' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'last_checked_at' => ['nullable', 'date'],
        ]);

        $data['website'] = Url::official($data['website'] ?? null);
        $data['youtube'] = \App\Http\Controllers\Mentor\ProfileController::youtubeId($data['youtube'] ?? null);
        $data['is_visible'] = $request->boolean('is_visible');
        $data['is_sample'] = $request->boolean('is_sample');

        return $data;
    }
}
