<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface Domain
 *
 * @property \App\Models\Contracts\Owner $owner
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface Domain
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model>
     */
    public function owner(): MorphTo;
}
