<?php

namespace App\Models;

use App\Models\Concerns\HasVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorProfile extends Model
{
    use HasVisibility;

    protected $guarded = ['id'];

    // Nomor WhatsApp tidak ikut keluar kalau model diubah jadi array/JSON.
    protected $hidden = ['whatsapp'];

    protected function casts(): array
    {
        return [
            'is_willing_to_help' => 'boolean',
            'is_sample' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequest::class);
    }

    public function scopeWilling(Builder $query): Builder
    {
        return $query->where('is_willing_to_help', true);
    }

    /**
     * Bersedia membantu DAN belum penuh. Mentor boleh membatasi jumlah pengajuan aktif
     * (max_active_requests); kalau sudah penuh, namanya tidak muncul di pilihan siswa.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_willing_to_help', true)->where(function (Builder $w) {
            $w->whereNull('max_active_requests')->orWhereRaw(
                '(select count(*) from review_requests rr where rr.mentor_profile_id = mentor_profiles.id and rr.status = ? and rr.created_at >= ?) < mentor_profiles.max_active_requests',
                [ReviewRequest::PENDING, ReviewRequest::cutoff()],
            );
        });
    }

    /**
     * Link wa.me, HANYA kalau mentor bersedia dan sudah mengisi nomornya.
     * Dipakai di halaman Applied milik siswa yang mengajukan, bukan di halaman publik.
     */
    public function whatsappUrl(): ?string
    {
        if (! $this->is_willing_to_help || blank($this->whatsapp)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return 'https://wa.me/'.$digits;
    }
}
