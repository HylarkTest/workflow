<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use App\Models\Concerns\CanBeFavorited;
use App\Models\Contracts\FeatureListItem;
use App\Models\Concerns\HasFeatureListItemMethods;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $url
 * @property \Illuminate\Support\Carbon $favorited_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \App\Models\LinkList $linkList
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\App\Models\Link>
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\LinkList, \App\Models\Link>
 */
class Link extends \Illuminate\Database\Eloquent\Model implements FeatureListItem, Sortable
{
    use CanBeFavorited;

    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\LinkList> */
    use HasFeatureListItemMethods;

    use IsSortable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'url',
        'space_id',
        'favorited_at',
    ];

    protected $casts = [
        'favorited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LinkList, \App\Models\Link>
     */
    public function linkList(): BelongsTo
    {
        return $this->belongsTo(LinkList::class, 'link_list_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LinkList, \App\Models\Link>
     */
    public function list(): BelongsTo
    {
        return $this->linkList();
    }

    public static function formatLinkListIdActionPayload(?int $linkListId): ?Deferred
    {
        return static::formatListIdActionPayload($linkListId);
    }
}
