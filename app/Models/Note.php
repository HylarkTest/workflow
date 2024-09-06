<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use MarkupUtils\TipTap;
use Notes\Models\Note as BaseNote;
use App\Models\Concerns\CanBeFavorited;
use App\Models\Contracts\FeatureListItem;
use App\Models\Concerns\HasFeatureListItemMethods;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use Mappings\Core\Documents\Contracts\ImageRepository;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * @property \App\Models\Notebook $notebook
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Notebook, \App\Models\Note> notebook()
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Notes\Models\Note>
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\Notebook, \App\Models\Note>
 */
class Note extends BaseNote implements FeatureListItem, Sortable
{
    use CanBeFavorited;

    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\Notebook> */
    use HasFeatureListItemMethods;

    use IsSortable;

    protected $fillable = [
        'space_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Notebook, \App\Models\Note>
     */
    public function list(): BelongsTo
    {
        return $this->notebook();
    }

    public static function formatTextActionPayload(?string $text): ?string
    {
        return $text ? (string) (new TipTap(json_decode($text, true, 512, \JSON_THROW_ON_ERROR)))->convertToPlaintext() : $text;
    }

    public static function formatNotebookIdActionPayload(?int $todoListId): ?Deferred
    {
        return static::formatListIdActionPayload($todoListId);
    }

    protected function secondarySearchableArray(): array
    {
        return array_merge([
            [
                'text' => (string) $this->plaintext,
                'map' => 'plaintext',
            ],
        ], $this->getAssigneesMappedForFinder());
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $note) {
            if ($note->isForceDeleting()) {
                $imageUrl = preg_quote(config('filesystems.disks.images.url'), '/');
                collect($note->tiptap->tiptap)
                    ->dot()
                    ->each(function ($value) use ($imageUrl) {
                        if (is_string($value) && preg_match("/^$imageUrl/", $value)) {
                            resolve(ImageRepository::class)->removeByUrl($value);
                        }
                    });
            }
        });
    }
}
