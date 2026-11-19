<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarshipController as PublicScholarshipController;
use App\Models\Scholarship;
use App\Support\Url;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $items = Scholarship::withoutGlobalScopes()
            ->when($q !== '', fn ($w) => $w->where('nama', 'like', '%'.PublicScholarshipController::escapeLike($q).'%'))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.scholarships.index', ['items' => $items, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.scholarships.form', ['item' => new Scholarship(['is_visible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Scholarship::create($this->validated($request));

        return redirect()->route('admin.scholarships.index')->with('status', 'Beasiswa ditambahkan.');
    }

    public function edit(int $scholarship): View
    {
        return view('admin.scholarships.form', ['item' => Scholarship::withoutGlobalScopes()->findOrFail($scholarship)]);
    }

    public function update(Request $request, int $scholarship): RedirectResponse
    {
        $item = Scholarship::withoutGlobalScopes()->findOrFail($scholarship);
        $item->update($this->validated($request));

        return redirect()->route('admin.scholarships.index')->with('status', 'Perubahan tersimpan.');
    }

    public function destroy(int $scholarship): RedirectResponse
    {
        Scholarship::withoutGlobalScopes()->findOrFail($scholarship)->delete();

        return redirect()->route('admin.scholarships.index')->with('status', 'Beasiswa dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'sub' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'string', 'max:255'],
            'posted' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'logo_text' => ['nullable', 'string', 'max:10'],
            'founded' => ['nullable', 'string', 'max:255'],
            'org_type' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) {
                if (filled($value) && Url::official($value) === null) {
                    $fail('Link resmi harus berupa alamat web (http atau https).');
                }
            }],
            'desc' => ['nullable', 'string', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'last_checked_at' => ['nullable', 'date'],
        ]);

        $data['website'] = Url::official($data['website'] ?? null);
        $data['is_visible'] = $request->boolean('is_visible');
        // Data yang diisi lewat halaman admin dianggap data asli, bukan contoh.
        $data['is_sample'] = $request->boolean('is_sample');

        return $data;
    }
}
