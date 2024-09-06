<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\Contracts\NotScoped;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $is_private
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle> $articles
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle> $popularArticles
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportFolder> $folders
 */
class SupportCategory extends Model implements NotScoped, Sortable
{
    use HasFactory;
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'order',
    ];

    protected $guarded = [];

    public function getConnectionName()
    {
        return config('hylark.support.database');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Support\SupportFolder>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(SupportFolder::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Support\SupportArticle>
     */
    public function articles(): HasManyThrough
    {
        return $this->hasManyThrough(SupportArticle::class, SupportFolder::class, 'category_id', 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Support\SupportArticle>
     */
    public function popularArticles(): HasManyThrough
    {
        return $this->articles()
            ->orderByDesc('views');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Support\SupportArticle>
     */
    public function allArticles(): HasManyThrough
    {
        return $this->articles()
            ->withoutGlobalScope('published');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportCategory>
     */
    public static function getCachedCategories(): Collection
    {
        $fetchCategories = static fn () => self::query()
            ->with([
                'folders.articles' => fn (HasMany $query) => $query->select(
                    'id',
                    'friendly_url',
                    'folder_id',
                    'title',
                    'content',
                    'views',
                    'thumbs_up',
                    'thumbs_down',
                    'created_at',
                ),
                'folders.articles.topics',
            ])
            ->get();

        if (! config('hylark.support.cache.enabled')) {
            return $fetchCategories();
        }

        return cache()->tags(['support'])->remember(self::cacheKey(), self::cacheTtl(), fn () => $fetchCategories());
    }

    protected static function boot()
    {
        parent::boot();

        foreach (['saved', 'deleted'] as $event) {
            static::$event(static function () {
                if (config('hylark.support.cache.enabled')) {
                    cache()->tags(['support'])->forget(self::cacheKey());
                }
            });
        }

        parent::addGlobalScope('public', static function ($query) {
            $query->where('is_public', true);
        });
    }

    protected static function cacheKey(): string
    {
        return config('hylark.support.cache.key').'.categories';
    }

    protected static function cacheTtl(): int
    {
        return config('hylark.support.cache.ttl');
    }
}
