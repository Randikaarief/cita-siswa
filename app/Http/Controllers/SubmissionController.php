<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CV dan esai milik siswa. Disimpan di disk PRIVAT (storage/app/private),
 * hanya pemilik yang bisa membuka. Hanya PDF, maksimal 2 MB, maksimal 10 berkas per siswa.
 */
class SubmissionController extends Controller
{
    public const MAX_FILES = 10;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:2048'],
            'type' => ['nullable', Rule::in(['cv', 'essay'])],
        ], [
            'file.max' => 'Ukuran file maksimal 2 MB.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.mimetypes' => 'File harus berformat PDF.',
        ]);

        $user = $request->user();

        if ($user->submissions()->count() >= self::MAX_FILES) {
            return back()->withErrors(['file' => 'Maksimal '.self::MAX_FILES.' berkas. Hapus salah satu dulu.']);
        }

        $file = $request->file('file');
        $title = Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 140, '');
        $path = $file->store('submissions/'.$user->id, 'local');

        $user->submissions()->create([
            'type' => $data['type'] ?? 'cv',
            'title' => $title !== '' ? $title : 'Berkas',
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        return back()->with('status', 'Berkas tersimpan.');
    }

    public function download(Request $request, Submission $submission): StreamedResponse
    {
        abort_unless($submission->user_id === $request->user()->id, 404);
        abort_unless(Storage::disk('local')->exists($submission->path), 404);

        return Storage::disk('local')->response(
            $submission->path,
            Str::slug($submission->title) ?: 'berkas',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.(Str::slug($submission->title) ?: 'berkas').'.pdf"'],
        );
    }

    public function destroy(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless($submission->user_id === $request->user()->id, 404);

        Storage::disk('local')->delete($submission->path);
        $submission->delete();

        return back()->with('status', 'Berkas dihapus.');
    }
}
