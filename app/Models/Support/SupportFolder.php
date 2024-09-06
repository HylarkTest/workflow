<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\Contracts\NotScoped;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportFolder> $folders
 * @property \App\Models\Support\SupportCategory $category
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle> $articles
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle> $allArticles
 */
class SupportFolder extends Model implements NotScoped, Sortable
{
    use HasFactory;
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'order',
        'sort_on_has_many' => true,
    ];

    protected $guarded = [];

    protected $touches = ['category'];

    public function getConnectionName()
    {
        return config('hylark.support.database');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Support\SupportFolder>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(__CLASS__, 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Support\SupportCategory, \App\Models\Support\SupportFolder>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id')
            ->withoutGlobalScope('public');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Support\SupportArticle>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(SupportArticle::class, 'folder_id')
            ->withoutGlobalScope('public');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Support\SupportArticle>
     */
    public function allArticles(): HasMany
    {
        return $this->articles()
            ->withoutGlobalScope('published');
    }

    public static function getCachedFolder(int $id): self
    {
        $fetchedFolder = static fn () => self::query()->findOrFail($id);

        if (! config('hylark.support.cache.enabled')) {
            return $fetchedFolder();
        }

        return cache()->tags(['support'])->remember(self::cachePrefix().$id, self::cacheTtl(), fn () => $fetchedFolder());
    }

    protected static function boot()
    {
        parent::boot();

        foreach (['saved', 'deleted'] as $event) {
            static::$event(static function (self $model) {
                if (config('hylark.support.cache.enabled')) {
                    cache()->tags(['support'])->forget(self::cachePrefix().$model->getKey());
                }
            });
        }
    }

    protected static function cachePrefix(): string
    {
        return config('hylark.support.cache.key').'.folder:';
    }

    protected static function cacheTtl(): int
    {
        return config('hylark.support.cache.ttl');
    }
}
