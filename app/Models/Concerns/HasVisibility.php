<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data yang is_visible = false tidak tampil di mana pun.
 * Dipakai oleh perintah `samples:purge --hide` untuk menyembunyikan data contoh.
 * Pakai Model::withoutGlobalScopes() untuk melihat semuanya.
 */
trait HasVisibility
{
    protected static function bootHasVisibility(): void
    {
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable().'.is_visible', true);
        });
    }
}
