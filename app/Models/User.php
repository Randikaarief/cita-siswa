<?php

namespace App\Models;

use App\Notifications\ResetPasswordId;
use App\Notifications\VerifyEmailId;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_MENTOR = 'mentor';
    public const ROLE_ADMIN = 'admin';

    /** Nilai bawaan, sama dengan default di database, supaya objek yang baru dibuat sudah lengkap. */
    protected $attributes = [
        'role' => self::ROLE_USER,
        'is_active' => true,
        'must_change_password' => false,
    ];

    /**
     * "role" sengaja TIDAK ada di sini supaya tidak bisa diubah lewat form.
     * Set peran secara eksplisit: $user->role = User::ROLE_MENTOR.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'headline',
        'experience',
        'education',
        'education_level',
        'website',
        'nationality',
        'birth_date',
        'gender',
        'phone',
        'location',
        'social_links',
        'notify_prefs',
        'is_profile_public',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'social_links' => 'array',
            'notify_prefs' => 'array',
            'is_profile_public' => 'boolean',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequest::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /** Profil mentor milik akun ini (hanya untuk role mentor). */
    public function mentorProfile(): HasOne
    {
        return $this->hasOne(MentorProfile::class)->withoutGlobalScopes();
    }

    /**
     * Verifikasi email hanya diwajibkan untuk siswa.
     * Akun mentor dan admin dibuat oleh admin/seeder, jadi selalu dianggap terverifikasi.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! $this->isStudent() || ! is_null($this->email_verified_at);
    }

    /** Email verifikasi dalam bahasa Indonesia. */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailId);
    }

    /** Email reset password dalam bahasa Indonesia. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordId($token));
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isMentor(): bool
    {
        return $this->role === self::ROLE_MENTOR;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Halaman utama menurut peran. */
    public static function homeFor(?string $role): string
    {
        return match ($role) {
            self::ROLE_MENTOR => '/mentor',
            self::ROLE_ADMIN => '/admin',
            default => '/',
        };
    }

    public function homeUrl(): string
    {
        return self::homeFor($this->role);
    }

    /** Foto profil milik sendiri (lewat route privat), atau null kalau belum ada. */
    public function avatarUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return route('settings.photo', ['v' => $this->updated_at?->timestamp]);
    }

    public function profileIsComplete(): bool
    {
        return filled($this->headline) && filled($this->education) && filled($this->nationality);
    }

    public function notifyPref(string $key, bool $default = false): bool
    {
        return (bool) ($this->notify_prefs[$key] ?? $default);
    }
}
