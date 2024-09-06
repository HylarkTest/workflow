<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property int $space_id
 * @property \Color\Color|null $color
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Link> $links
 * @property \App\Models\Space $space
 *
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Link, \App\Models\LinkList>
 */
class LinkList extends Model implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Link, \App\Models\LinkList> */
    use HasFeatureListMethods;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Link>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Link>
     */
    public function children(): HasMany
    {
        return $this->links();
    }
}
