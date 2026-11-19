<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Submission extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewRequests(): BelongsToMany
    {
        return $this->belongsToMany(ReviewRequest::class, 'review_request_submission');
    }

    public function sizeLabel(): string
    {
        $mb = $this->size / 1048576;

        return $mb >= 0.1
            ? number_format($mb, 1).' MB'
            : max(1, (int) round($this->size / 1024)).' KB';
    }
}
