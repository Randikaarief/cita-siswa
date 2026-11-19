<?php

namespace App\Support;

/**
 * Warna kotak logo kartu beasiswa/lomba/mentor. Dipilih dari id supaya tetap sama tiap tampil.
 * Class ditulis utuh di sini agar terbaca oleh Tailwind.
 */
class Palette
{
    private const TILES = [
        'bg-brand-500 text-white',
        'bg-sun-400 text-ink',
        'bg-emerald-600 text-white',
        'bg-rose-500 text-white',
        'bg-violet-600 text-white',
        'bg-sky-600 text-white',
        'bg-orange-500 text-white',
    ];

    public static function tile(int|string|null $seed): string
    {
        return self::TILES[abs(crc32((string) $seed)) % count(self::TILES)];
    }

    /** Huruf awal untuk kotak logo / foto profil. */
    public static function initial(?string $text): string
    {
        $text = trim((string) $text);

        return $text === '' ? '?' : mb_strtoupper(mb_substr($text, 0, 1));
    }
}
