<?php

namespace App\Models;

use App\Models\Concerns\HasVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasVisibility;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_sample' => 'boolean', 'is_visible' => 'boolean'];
    }

    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class);
    }
}
