<?php

namespace App\Support;

class Url
{
    /**
     * Ubah isian website menjadi URL http(s) yang aman dipakai untuk redirect.
     * "www.contoh.com" menjadi "https://www.contoh.com". Skema lain (javascript:, dll) ditolak.
     */
    public static function official(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (! preg_match('#^[a-z][a-z0-9+.\-]*://#i', $value)) {
            if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $value) && ! preg_match('#^[^/]+:\d+#', $value)) {
                return null; // mis. javascript:..., mailto:...
            }
            $value = 'https://'.$value;
        }

        if (! preg_match('#^https?://#i', $value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $value;
    }
}
