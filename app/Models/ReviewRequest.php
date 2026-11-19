<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReviewRequest extends Model
{
    public const PENDING = 'pending';
    public const REPLIED = 'replied';
    public const DECLINED = 'declined';
    public const CANCELLED = 'cancelled';
    public const EXPIRED = 'expired'; // tidak disimpan: dihitung dari pengajuan pending yang terlalu lama

    public const LABELS = [
        self::PENDING => 'Menunggu',
        self::REPLIED => 'Sudah dibalas',
        self::DECLINED => 'Belum bisa membantu',
        self::CANCELLED => 'Dibatalkan',
        self::EXPIRED => 'Belum ada balasan',
    ];

    public const BADGES = [
        self::PENDING => 'badge-yellow',
        self::REPLIED => 'badge-green',
        self::DECLINED => 'badge-red',
        self::CANCELLED => 'badge-gray',
        self::EXPIRED => 'badge-gray',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['replied_at' => 'datetime'];
    }

    /** Pengajuan pending yang dibuat sebelum waktu ini dianggap kedaluwarsa. */
    public static function cutoff(): Carbon
    {
        return now()->subDays((int) config('site.request_expire_days', 14));
    }

    /** Pengajuan yang masih menunggu dan belum kedaluwarsa. Hanya yang "terbuka" yang memberi mentor akses ke berkas. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::PENDING)->where('created_at', '>=', self::cutoff());
    }

    public function isOpen(): bool
    {
        return $this->status === self::PENDING && $this->created_at->gte(self::cutoff());
    }

    public function effectiveStatus(): string
    {
        if ($this->status === self::PENDING && ! $this->isOpen()) {
            return self::EXPIRED;
        }

        return $this->status;
    }

    public function statusLabel(): string
    {
        return self::LABELS[$this->effectiveStatus()] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::BADGES[$this->effectiveStatus()] ?? 'badge-gray';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id')->withoutGlobalScopes();
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class)->withoutGlobalScopes();
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class)->withoutGlobalScopes();
    }

    /** Berkas (CV/esai) yang dibagikan siswa pada pengajuan ini. */
    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'review_request_submission');
    }
}
