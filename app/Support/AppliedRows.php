<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Competition;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Baris halaman Riwayat dan dashboard milik satu siswa.
 * Satu baris per beasiswa atau lomba: nama + (kalau ada) pengajuan masukan ke mentor.
 * Sumber: riwayat klik "Buka situs resmi" (applications) dan pengajuan masukan (review_requests).
 */
class AppliedRows
{
    public static function count(User $user): int
    {
        return self::forUser($user)->count();
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function forUser(User $user, ?int $limit = null): Collection
    {
        $apps = Application::where('user_id', $user->id)->get();
        $requests = ReviewRequest::with(['mentor', 'submissions'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get();

        $entries = []; // "type:id" => ['app' => ?Application, 'request' => ?ReviewRequest]

        foreach ($apps as $app) {
            $entries[$app->target_type.':'.$app->target_id] = ['app' => $app, 'request' => null];
        }

        foreach ($requests as $req) {
            $type = $req->scholarship_id ? Application::SCHOLARSHIP : Application::COMPETITION;
            $id = $req->scholarship_id ?: $req->competition_id;
            if (! $id) {
                continue;
            }
            $key = $type.':'.$id;
            $entries[$key] ??= ['app' => null, 'request' => null];
            // $requests terurut terbaru dulu, jadi yang pertama dipakai.
            $entries[$key]['request'] ??= $req;
        }

        $scholarships = Scholarship::withoutGlobalScopes()
            ->whereIn('id', self::idsOf($entries, Application::SCHOLARSHIP))->get()->keyBy('id');
        $competitions = Competition::withoutGlobalScopes()
            ->whereIn('id', self::idsOf($entries, Application::COMPETITION))->get()->keyBy('id');

        $rows = collect();

        foreach ($entries as $key => $entry) {
            [$type, $id] = explode(':', $key);
            $isCompetition = $type === Application::COMPETITION;
            $target = $isCompetition ? $competitions->get((int) $id) : $scholarships->get((int) $id);
            if (! $target) {
                continue;
            }

            /** @var ?ReviewRequest $req */
            $req = $entry['request'];
            $sortAt = collect([$entry['app']?->last_opened_at, $req?->created_at])->filter()->max();
            $status = $req?->effectiveStatus();
            $showWhatsapp = $req && in_array($status, [ReviewRequest::PENDING, ReviewRequest::REPLIED], true);

            $rows->push([
                'name' => $target->nama,
                'kind' => $type,
                'logo' => $isCompetition ? Palette::initial($target->nama) : ($target->logo_text ?: Palette::initial($target->nama)),
                'tile' => Palette::tile($isCompetition ? 'c'.$target->id : $target->id),
                'detail_url' => ($isCompetition ? '/lomba/' : '/find-scholarship/').$target->id,
                'date' => $sortAt?->translatedFormat('d M Y, H:i'),
                'sort_at' => $sortAt,

                'mentor' => $req?->mentor?->nama,
                'request_id' => $req?->id,
                'note' => $req?->note,
                'feedback' => $req?->feedback,
                'decline_reason' => $req?->decline_reason,
                'files' => $req ? $req->submissions->pluck('title')->all() : [],
                'can_cancel' => $req?->isOpen() ?? false,
                'wa_url' => $showWhatsapp ? $req->mentor?->whatsappUrl() : null,

                'status_key' => $status ?? 'opened',
                'status' => $req ? $req->statusLabel() : 'Dibuka',
                'status_badge' => $req ? $req->statusBadge() : 'badge-blue',
            ]);
        }

        $rows = $rows->sortByDesc(fn ($r) => $r['sort_at']?->timestamp ?? 0)->values();

        return $limit ? $rows->take($limit)->values() : $rows;
    }

    private static function idsOf(array $entries, string $type): array
    {
        $ids = [];
        foreach (array_keys($entries) as $key) {
            [$t, $id] = explode(':', $key);
            if ($t === $type) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
