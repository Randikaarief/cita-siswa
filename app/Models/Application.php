<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan klik "Apply Now" milik siswa. Hanya riwayat klik, bukan bukti pendaftaran.
 */
class Application extends Model
{
    public const SCHOLARSHIP = 'scholarship';
    public const COMPETITION = 'competition';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['last_opened_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Beasiswa atau lomba yang dituju. */
    public function target(): Scholarship|Competition|null
    {
        return $this->target_type === self::COMPETITION
            ? Competition::withoutGlobalScopes()->find($this->target_id)
            : Scholarship::withoutGlobalScopes()->find($this->target_id);
    }
}
