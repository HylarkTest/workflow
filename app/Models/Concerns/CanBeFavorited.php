<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CanBeFavorited
{
    public function isFavorite(): bool
    {
        return $this->favorited_at !== null;
    }

    public function favorite(): void
    {
        $this->favorited_at = now();
        $this->save();
    }
}
