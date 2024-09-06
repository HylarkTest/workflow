<?php

declare(strict_types=1);

namespace Notes\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait HasNotes
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasNotes
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Notes\Models\Note>
     */
    public function notes(): MorphToMany
    {
        return $this->morphToMany(Note::class, 'notable');
    }
}
