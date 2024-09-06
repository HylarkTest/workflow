<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Pin, \App\Models\Pinboard>
 */
class Pinboard extends Model implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Pin, \App\Models\Pinboard> */
    use HasFeatureListMethods;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pin>
     */
    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pin>
     */
    public function children(): HasMany
    {
        return $this->pins();
    }
}
