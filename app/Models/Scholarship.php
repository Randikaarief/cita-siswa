<?php

namespace App\Models;

use App\Models\Concerns\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasVisibility;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_sample' => 'boolean',
            'is_visible' => 'boolean',
            'last_checked_at' => 'date',
        ];
    }

    /** Link resmi dengan skema http(s), atau null kalau belum diisi. */
    public function officialUrl(): ?string
    {
        return \App\Support\Url::official($this->website);
    }
}
