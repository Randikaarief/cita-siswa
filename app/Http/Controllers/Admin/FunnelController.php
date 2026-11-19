<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CaptureUtm;
use App\Models\Campaign;
use App\Models\CampaignVisit;
use App\Models\ReviewRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Corong pemasaran (sesuai proposal): klik link Instagram -> pendaftaran -> unggah berkas.
 *  - Klik            : pengunjung unik per kampanye yang datang lewat link UTM.
 *  - Pendaftaran     : akun siswa dengan email yang sudah terverifikasi.
 *  - Pengunggah      : siswa yang sudah mengunggah minimal satu berkas.
 *  - Diteruskan      : siswa yang pernah membuka situs resmi beasiswa/lomba.
 *  - ACR = pengunggah / klik x 100%.   CTR = klik / impresi x 100% (impresi diisi manual).
 * Hanya angka agregat. Tidak ada data pribadi siswa dan tidak ada berkas.
 */
class FunnelController extends Controller
{
    public function index(): View
    {
        $visits = CampaignVisit::query()
            ->selectRaw('utm_campaign, count(*) as clicks, min(utm_source) as source, min(utm_medium) as medium')
            ->groupBy('utm_campaign')->get()->keyBy('utm_campaign');

        $users = User::query()
            ->where('role', User::ROLE_USER)->whereNotNull('utm_source')
            ->selectRaw("coalesce(utm_campaign, '') as campaign,
                sum(case when email_verified_at is not null then 1 else 0 end) as registrations,
                sum(exists (select 1 from submissions s where s.user_id = users.id)) as uploaders,
                sum(exists (select 1 from applications a where a.user_id = users.id)) as forwarded,
                sum(exists (select 1 from review_requests r where r.user_id = users.id)) as requested")
            ->groupBy('campaign')->get()->keyBy('campaign');

        $impressions = Campaign::pluck('impressions', 'utm_campaign');

        $names = $visits->keys()->merge($users->keys())->merge($impressions->keys())->unique()->sort()->values();

        $rows = $names->map(function ($name) use ($visits, $users, $impressions) {
            $clicks = (int) ($visits[$name]->clicks ?? 0);
            $uploaders = (int) ($users[$name]->uploaders ?? 0);
            $imp = $impressions[$name] ?? null;

            return [
                'campaign' => $name,
                'source' => $visits[$name]->source ?? null,
                'medium' => $visits[$name]->medium ?? null,
                'impressions' => $imp,
                'clicks' => $clicks,
                'registrations' => (int) ($users[$name]->registrations ?? 0),
                'uploaders' => $uploaders,
                'forwarded' => (int) ($users[$name]->forwarded ?? 0),
                'requested' => (int) ($users[$name]->requested ?? 0),
                'ctr' => $imp ? $clicks / $imp * 100 : null,
                'acr' => $clicks ? $uploaders / $clicks * 100 : null,
            ];
        });

        $totalClicks = $rows->sum('clicks');
        $totalUploaders = $rows->sum('uploaders');

        $statusCounts = [
            'Menunggu' => ReviewRequest::open()->count(),
            'Sudah dibalas' => ReviewRequest::where('status', ReviewRequest::REPLIED)->count(),
            'Belum bisa membantu' => ReviewRequest::where('status', ReviewRequest::DECLINED)->count(),
            'Dibatalkan' => ReviewRequest::where('status', ReviewRequest::CANCELLED)->count(),
            'Belum ada balasan' => ReviewRequest::where('status', ReviewRequest::PENDING)->where('created_at', '<', ReviewRequest::cutoff())->count(),
        ];

        return view('admin.funnel', [
            'rows' => $rows,
            'totals' => [
                'clicks' => $totalClicks,
                'registrations' => $rows->sum('registrations'),
                'uploaders' => $totalUploaders,
                'forwarded' => $rows->sum('forwarded'),
                'acr' => $totalClicks ? $totalUploaders / $totalClicks * 100 : null,
            ],
            'allStudents' => User::where('role', User::ROLE_USER)->count(),
            'statusCounts' => $statusCounts,
        ]);
    }

    /** Impresi dari dashboard Instagram, diisi manual per kampanye. */
    public function impressions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'campaign' => ['required', 'string', 'max:100'],
            'impressions' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        $name = CaptureUtm::clean($data['campaign']);
        if ($name === '') {
            return back()->withErrors(['campaign' => 'Nama kampanye tidak valid.']);
        }

        Campaign::updateOrCreate(['utm_campaign' => $name], ['impressions' => $data['impressions'] ?? null]);

        return back()->with('status', 'Impresi disimpan.');
    }
}
