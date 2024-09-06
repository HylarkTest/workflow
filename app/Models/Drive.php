<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Relationships
 *
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 *
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Document, \App\Models\Drive>
 */
class Drive extends Model implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Document, \App\Models\Drive> */
    use HasFeatureListMethods;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Document>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function children(): HasMany
    {
        return $this->documents();
    }
}
