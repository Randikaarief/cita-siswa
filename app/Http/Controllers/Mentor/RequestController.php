<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ReviewRequest;
use App\Models\Submission;
use App\Notifications\SimpleNotice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pengajuan "Minta masukan" yang ditujukan ke mentor ini.
 * - Mentor HANYA melihat nama siswa, catatan, dan berkas yang dibagikan (bukan email, telepon, dll).
 * - Berkas hanya bisa dibuka selama pengajuan masih TERBUKA (belum dibalas, ditolak, dibatalkan, atau kedaluwarsa).
 * - Mentor membantu secara sukarela: boleh membalas, boleh menolak.
 */
class RequestController extends Controller
{
    public const STATUS_FILTERS = ['pending', 'replied', 'declined', 'cancelled', 'expired'];

    /** Query dasar: hanya kolom nama pada data siswa yang dimuat. */
    public static function query(int $mentorProfileId): Builder
    {
        return ReviewRequest::query()
            ->where('mentor_profile_id', $mentorProfileId)
            ->with(['user:id,name', 'scholarship', 'competition'])
            ->withCount('submissions');
    }

    public function index(Request $request): View
    {
        $profile = $request->user()->mentorProfile;
        $status = in_array($request->query('status'), self::STATUS_FILTERS, true) ? $request->query('status') : null;

        $query = $profile ? self::query($profile->id) : ReviewRequest::query()->whereRaw('1 = 0');

        match ($status) {
            'pending' => $query->open(),
            'expired' => $query->where('status', ReviewRequest::PENDING)->where('created_at', '<', ReviewRequest::cutoff()),
            null => null,
            default => $query->where('status', $status),
        };

        return view('mentor.requests', [
            'profile' => $profile,
            'requests' => $query->latest()->paginate(15)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function show(Request $request, ReviewRequest $reviewRequest): View
    {
        $this->authorizeOwn($request, $reviewRequest);

        $reviewRequest->load(['user:id,name', 'scholarship', 'competition', 'submissions:id,user_id,type,title,size']);

        return view('mentor.request', ['r' => $reviewRequest]);
    }

    public function reply(Request $request, ReviewRequest $reviewRequest): RedirectResponse
    {
        $this->authorizeOwn($request, $reviewRequest);

        if (! $reviewRequest->isOpen()) {
            return redirect()->route('mentor.requests.show', $reviewRequest)->with('status', 'Pengajuan ini sudah ditutup.');
        }

        $data = $request->validate([
            'feedback' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'feedback.required' => 'Tulis masukanmu dulu.',
            'feedback.min' => 'Masukan terlalu pendek.',
            'feedback.max' => 'Masukan maksimal 2.000 karakter.',
        ]);

        $reviewRequest->update([
            'status' => ReviewRequest::REPLIED,
            'feedback' => $data['feedback'],
            'replied_at' => now(),
        ]);

        $this->notifyStudent($reviewRequest, $request->user()->mentorProfile->nama.' sudah membalas', 'Sudah dibalas');

        return redirect()->route('mentor.requests')->with('status', 'Masukan terkirim. Akses ke berkas siswa sudah ditutup.');
    }

    public function decline(Request $request, ReviewRequest $reviewRequest): RedirectResponse
    {
        $this->authorizeOwn($request, $reviewRequest);

        if (! $reviewRequest->isOpen()) {
            return redirect()->route('mentor.requests.show', $reviewRequest)->with('status', 'Pengajuan ini sudah ditutup.');
        }

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $reviewRequest->update([
            'status' => ReviewRequest::DECLINED,
            'decline_reason' => $data['reason'] ?? null,
        ]);

        $this->notifyStudent($reviewRequest, $request->user()->mentorProfile->nama.' belum bisa membantu', 'Coba mentor lain');

        return redirect()->route('mentor.requests')->with('status', 'Pengajuan ditolak dengan baik. Akses ke berkas siswa sudah ditutup.');
    }

    /** Berkas hanya bisa dibuka kalau ada pengajuan TERBUKA ke mentor ini yang membagikannya. */
    public function file(Request $request, Submission $submission): StreamedResponse
    {
        $profile = $request->user()->mentorProfile;

        $allowed = $profile && ReviewRequest::open()
            ->where('mentor_profile_id', $profile->id)
            ->whereHas('submissions', fn ($q) => $q->where('submissions.id', $submission->id))
            ->exists();

        abort_unless($allowed && Storage::disk('local')->exists($submission->path), 404);

        $name = (Str::slug($submission->title) ?: 'berkas').'.pdf';

        return Storage::disk('local')->response($submission->path, $name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }

    private function authorizeOwn(Request $request, ReviewRequest $reviewRequest): void
    {
        $profile = $request->user()->mentorProfile;
        abort_unless($profile && $reviewRequest->mentor_profile_id === $profile->id, 404);
    }

    private function notifyStudent(ReviewRequest $reviewRequest, string $title, string $badge): void
    {
        $target = $reviewRequest->scholarship ?? $reviewRequest->competition;

        $reviewRequest->user?->notify(new SimpleNotice($title, $target?->nama, '/riwayat', $badge));
    }
}
