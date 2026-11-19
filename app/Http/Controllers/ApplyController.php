<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Competition;
use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Tombol "Apply Now": mencatat klik lalu mengarahkan ke situs RESMI penyelenggara.
 * Pendaftaran sebenarnya terjadi di situs resmi; ini hanya riwayat klik.
 */
class ApplyController extends Controller
{
    public function go(Request $request, string $type, int $id): RedirectResponse
    {
        abort_unless(in_array($type, [Application::SCHOLARSHIP, Application::COMPETITION], true), 404);

        $target = $type === Application::COMPETITION
            ? Competition::findOrFail($id)
            : Scholarship::findOrFail($id);

        $url = $target->officialUrl();

        if ($url === null) {
            return back()->with('status', 'Link resmi belum tersedia. Cek langsung di situs penyelenggara.');
        }

        $application = Application::firstOrNew([
            'user_id' => $request->user()->id,
            'target_type' => $type,
            'target_id' => $target->id,
        ]);
        $application->last_opened_at = now(); // klik berulang hanya memperbarui waktu
        $application->save();

        return redirect()->away($url);
    }
}
