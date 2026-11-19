<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * "Minta masukan mentor". Mentor membantu secara sukarela, balasan tidak dijamin.
 * Siswa memilih 1 sampai 3 berkas miliknya; hanya mentor yang dipilih yang bisa membukanya,
 * dan hanya selama pengajuan masih terbuka.
 */
class ReviewRequestController extends Controller
{
    public const MAX_FILES = 3;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_type' => ['required', Rule::in([Application::SCHOLARSHIP, Application::COMPETITION])],
            'target_id' => ['required', 'integer'],
            'mentor_profile_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
            'submissions' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'submissions.*' => ['integer', 'distinct'],
        ], [
            'submissions.required' => 'Pilih minimal 1 berkas yang ingin dibagikan ke mentor.',
            'submissions.min' => 'Pilih minimal 1 berkas yang ingin dibagikan ke mentor.',
            'submissions.max' => 'Maksimal '.self::MAX_FILES.' berkas per pengajuan.',
        ]);

        $target = $data['target_type'] === Application::COMPETITION
            ? Competition::findOrFail($data['target_id'])
            : Scholarship::findOrFail($data['target_id']);

        // Hanya mentor yang bersedia dan belum penuh.
        $mentor = MentorProfile::available()->find($data['mentor_profile_id']);
        if (! $mentor) {
            return back()->withErrors(['mentor_profile_id' => 'Mentor ini sedang tidak tersedia. Coba pilih mentor lain.'])->withInput();
        }

        $user = $request->user();

        // Berkas harus milik siswa sendiri.
        $fileIds = $user->submissions()->whereIn('id', $data['submissions'])->pluck('id');
        if ($fileIds->count() !== count($data['submissions'])) {
            return back()->withErrors(['submissions' => 'Berkas tidak ditemukan.'])->withInput();
        }

        $targetColumn = $data['target_type'] === Application::COMPETITION ? 'competition_id' : 'scholarship_id';

        $alreadyOpen = ReviewRequest::open()
            ->where('user_id', $user->id)
            ->where('mentor_profile_id', $mentor->id)
            ->where($targetColumn, $target->id)
            ->exists();

        if ($alreadyOpen) {
            return redirect('/riwayat')->with('status', 'Permintaanmu ke mentor ini masih menunggu balasan.');
        }

        DB::transaction(function () use ($user, $mentor, $target, $targetColumn, $data, $fileIds) {
            $review = ReviewRequest::create([
                'user_id' => $user->id,
                'mentor_profile_id' => $mentor->id,
                $targetColumn => $target->id,
                'note' => $data['note'] ?? null,
                'status' => ReviewRequest::PENDING,
            ]);
            $review->submissions()->attach($fileIds->all());

            // Pastikan beasiswa ini muncul di Riwayat.
            Application::firstOrCreate(
                ['user_id' => $user->id, 'target_type' => $data['target_type'], 'target_id' => $target->id],
                ['last_opened_at' => now()],
            );
        });

        return redirect('/riwayat')->with('status', 'Permintaan masukan dikirim. Mentor membantu secara sukarela, jadi balasan tidak dijamin.');
    }

    /** Siswa membatalkan pengajuannya. Akses mentor ke berkas langsung tertutup. */
    public function cancel(Request $request, ReviewRequest $reviewRequest): RedirectResponse
    {
        abort_unless($reviewRequest->user_id === $request->user()->id, 404);

        if (! $reviewRequest->isOpen()) {
            return back()->with('status', 'Pengajuan ini sudah ditutup.');
        }

        $reviewRequest->update(['status' => ReviewRequest::CANCELLED]);

        return back()->with('status', 'Pengajuan dibatalkan. Akses mentor ke berkasmu sudah ditutup.');
    }
}
