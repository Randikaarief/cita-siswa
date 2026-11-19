<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SimpleNotice;
use App\Support\Url;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Pengumuman yang muncul di halaman Alert siswa. */
class AnnouncementController extends Controller
{
    public function create(): View
    {
        return view('admin.announcements');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'badge' => ['nullable', 'string', 'max:30'],
            'url' => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) {
                $ok = preg_match('#^/(?!/)#', (string) $value) || Url::official($value) !== null;
                if (filled($value) && ! $ok) {
                    $fail('Link harus diawali "/" (halaman di website ini) atau berupa alamat web http/https.');
                }
            }],
            'target' => ['required', Rule::in(['all', 'email'])],
            'email' => ['required_if:target,email', 'nullable', 'email'],
        ]);

        $notice = new SimpleNotice(
            $data['title'],
            $data['subtitle'] ?? null,
            filled($data['url'] ?? null) ? (str_starts_with($data['url'], '/') ? $data['url'] : Url::official($data['url'])) : null,
            $data['badge'] ?? null,
        );

        if ($data['target'] === 'email') {
            $student = User::where('role', User::ROLE_USER)->where('email', $data['email'])->first();
            if (! $student) {
                return back()->withErrors(['email' => 'Tidak ada siswa dengan email itu.'])->withInput();
            }
            $student->notify($notice);
            $count = 1;
        } else {
            $count = 0;
            User::where('role', User::ROLE_USER)->where('is_active', true)->chunkById(200, function ($students) use ($notice, &$count) {
                Notification::send($students, $notice);
                $count += $students->count();
            });
        }

        return back()->with('status', "Pengumuman terkirim ke {$count} siswa.");
    }
}
