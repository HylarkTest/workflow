<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use App\Models\Concerns\CanBeFavorited;
use App\Models\Contracts\FeatureListItem;
use LighthouseHelpers\Core\ModelBatchLoader;
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
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $favorited_at
 *
 * Relationships
 * @property \App\Models\Pinboard $pinboard
 * @property \App\Models\Document $image
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\App\Models\Pin>
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\Pinboard, \App\Models\Pin>
 */
class Pin extends \Illuminate\Database\Eloquent\Model implements FeatureListItem, Sortable
{
    use CanBeFavorited;

    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\Pinboard> */
    use HasFeatureListItemMethods;

    use IsSortable;

    public array $deleteCascadeRelationships = [
        'image',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'space_id',
        'name',
        'description',
        'favorited_at',
        'document_id',
    ];

    protected $casts = [
        'favorited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Pinboard, \App\Models\Pin>
     */
    public function pinboard(): BelongsTo
    {
        return $this->belongsTo(Pinboard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Pinboard, \App\Models\Pin>
     */
    public function list(): BelongsTo
    {
        return $this->pinboard();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Image, \App\Models\Pin>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'document_id');
    }

    public static function formatPinboardIdActionPayload(?int $pinboardId): ?Deferred
    {
        return static::formatListIdActionPayload($pinboardId);
    }

    public static function formatDocumentIdActionPayload(?int $documentId): ?Deferred
    {
        return $documentId ? ModelBatchLoader::instanceFromModel(
            Image::class
        )->loadAndResolve(
            $documentId, [],
            fn (?Image $image): ?string => $image?->filename
        ) : null;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $pin) {
            if ($pin->wasChanged('document_id')) {
                $oldImageId = $pin->getOriginal('document_id');
                if ($oldImageId && $oldImageId !== $pin->getAttribute('document_id')) {
                    /** @var \App\Models\Image $oldImage */
                    $oldImage = Image::query()->find($oldImageId);
                    $oldImage->delete();
                }
            }
        });
    }
}
