<?php

namespace App\Http\Middleware;

use App\Models\CampaignVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat kunjungan dari link kampanye (?utm_source=...&utm_medium=...&utm_campaign=...).
 * - Satu baris per pengunjung per kampanye. Alamat IP TIDAK disimpan.
 * - Sumber diingat lewat cookie (config site.utm_days hari) dan disimpan ke akun saat mendaftar.
 * - Berjalan sebelum pengalihan ke halaman login, jadi tamu yang diarahkan ke /login tetap terhitung.
 */
class CaptureUtm
{
    public const VISITOR_COOKIE = 'cs_visitor';
    public const UTM_COOKIE = 'cs_utm';

    public static function clean(mixed $value): string
    {
        $value = Str::lower(trim((string) $value));
        $value = preg_replace('/\s+/', '-', $value);

        return Str::limit(preg_replace('/[^a-z0-9._\-]/', '', $value), 100, '');
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && is_string($request->query('utm_source')) && $request->query('utm_source') !== '') {
            $utm = [
                'source' => self::clean($request->query('utm_source')),
                'medium' => self::clean($request->query('utm_medium', '')),
                'campaign' => self::clean($request->query('utm_campaign', '')),
            ];

            if ($utm['source'] !== '') {
                $visitor = (string) $request->cookie(self::VISITOR_COOKIE);
                if (! Str::isUuid($visitor)) {
                    $visitor = (string) Str::uuid();
                }

                CampaignVisit::firstOrCreate(
                    [
                        'visitor_id' => $visitor,
                        'utm_source' => $utm['source'],
                        'utm_medium' => $utm['medium'],
                        'utm_campaign' => $utm['campaign'],
                    ],
                    ['landing_path' => Str::limit('/'.ltrim($request->path(), '/'), 255, ''), 'created_at' => now()],
                );

                $minutes = (int) config('site.utm_days', 30) * 1440;
                Cookie::queue(self::VISITOR_COOKIE, $visitor, $minutes);
                Cookie::queue(self::UTM_COOKIE, json_encode($utm), $minutes);
            }
        }

        return $next($request);
    }
}
