<?php

declare(strict_types=1);

namespace App\Models\Support;

use MarkupUtils\HTML;
use Laravel\Nova\Nova;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Contracts\NotScoped;
use Spatie\EloquentSortable\Sortable;
use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Attributes
 *
 * @property int $id
 * @property string $title
 * @property string $edited_by
 * @property string $friendly_url
 * @property string $folder
 * @property string $content
 * @property int $views
 * @property int $thumbs_up
 * @property int $thumbs_down
 * @property int|null $latest_id
 * @property \Illuminate\Support\Carbon|null $live_at
 * @property \App\Models\Support\ArticleStatus $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Getters & Setters
 * @property string $stripped_content
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportTopic> $topics
 * @property \App\Models\Support\SupportArticle|null $parent
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle> $versions
 */
class SupportArticle extends Model implements NotScoped, Sortable
{
    use HasFactory;
    use Searchable;
    use SortableTrait;

    public ?string $previousContent = null;

    public array $sortable = [
        'order_column_name' => 'order',
        'sort_on_has_many' => true,
    ];

    protected $casts = [
        'status' => ArticleStatus::class,
        'live_at' => 'datetime',
    ];

    protected $guarded = [];

    protected $touches = ['folder'];

    public function searchableWith(): ?array
    {
        return ['topics'];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'friendly_url' => $this->friendly_url,
            'content' => $this->stripped_content,
            'topics' => $this->topics->map(static fn (SupportTopic $topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
            ])->toArray(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished() && $this->isLive();
    }

    public function getConnectionName()
    {
        return config('hylark.support.database');
    }

    public static function generateUrl(string $friendlyUrl): string
    {
        if (config('hylark.support.database') === 'resources') {
            $url = config('hylark.production_url');
        } else {
            $url = config('app.url');
        }

        return "$url/support/article/$friendlyUrl";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function strippedContent(): Attribute
    {
        return Attribute::get(function (): string {
            return (string) (new HTML($this->content))->convertToPlaintext();
        })->shouldCache();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function title(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes = []): array {
            $toSet = ['title' => $value];
            $this->attributes['title'] = $value;

            if (! ($attributes['friendly_url'] ?? false)) {
                $toSet['friendly_url'] = Str::of($value)->slug()->value();
                $this->attributes['friendly_url'] = $toSet['friendly_url'];
            }

            return $toSet;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function friendlyUrl(): Attribute
    {
        /** @phpstan-ignore-next-line What? */
        return Attribute::set(function (?string $value, array $attributes = []): string {
            return $value ?: Str::of($attributes['title'] ?? '')->slug()->value();
        });
    }

    public function url(?string $friendlyUrl = null): string
    {
        $friendlyUrl = $friendlyUrl ?: $this->friendly_url;

        return self::generateUrl($friendlyUrl ?: '');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Support\SupportFolder, \App\Models\Support\SupportArticle>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(SupportFolder::class, 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<self>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'latest_id')->withoutGlobalScopes([
            'published',
            'live',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<self, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'latest_id')->withoutGlobalScopes([
            'published',
            'live',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Support\SupportTopic>
     */
    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(SupportTopic::class, 'support_article_topic', 'article_id', 'topic_id');
    }

    public static function findByIdOrUrl(string $id): self
    {
        $column = is_numeric($id) ? 'id' : 'friendly_url';

        return self::query()->where($column, $id)->firstOrFail();
    }

    public static function getCachedArticle(string $id): self
    {
        return self::getCachedFromCallback(self::cachePrefix().'article:'.$id, static fn () => self::findByIdOrUrl($id));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public static function getCachedArticles(): Collection
    {
        return self::getCachedFromQuery('all', null, ['articles']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public static function getCachedRecommendedArticles(?array $topics = []): Collection
    {
        $cacheKey = 'recommended';
        if ($topics) {
            $topics = SupportTopic::sanitizeTopicIds($topics);
            sort($topics);
            $cacheKey .= '.'.implode('.', $topics);
        }

        return self::getCachedFromQuery(
            $cacheKey,
            static fn (Builder $query) => $query->selectRaw('thumbs_up - thumbs_down as score')
                ->when($topics, static fn (Builder $query) => $query->whereHas('topics', static fn (Builder $query) => $query->whereIn('support_topics.id', $topics)))
                ->orderByDesc('score')
                ->orderByDesc('views')
                ->take(3),
            ['articles']
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public static function getCachedMostRecentArticles(?array $topics = []): Collection
    {
        $cacheKey = 'recent';
        if ($topics) {
            $topics = SupportTopic::sanitizeTopicIds($topics);
            sort($topics);
            $cacheKey .= '.'.implode('.', $topics);
        }

        return self::getCachedFromQuery(
            $cacheKey,
            static fn (Builder $query) => $query->orderByDesc('created_at')
                ->when($topics, static fn (Builder $query) => $query->whereHas('topics', static fn (Builder $query) => $query->whereIn('support_topics.id', $topics)))
                ->take(3),
            ['articles']
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function articleLinks(): \Illuminate\Support\Collection
    {
        preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>/', $this->content ?: '', $matches);
        /** @var array{ 0: string[], 1: string[] }|false $matches */
        $prefix = self::generateUrl('');

        return collect($matches[1] ?? [])
            ->unique()
            ->filter(static fn ($match) => Str::startsWith($match, [$prefix, '/']))
            ->map(static fn ($match) => str_replace($prefix, '', $match));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public function getArticlesLinkedTo(): Collection
    {
        $articleLinks = $this->articleLinks();
        if ($articleLinks->isNotEmpty()) {
            return self::query()->whereIn('friendly_url', $articleLinks->toArray())->get();
        }

        return $this->newCollection();
    }

    public function hasInvalidArticleLinks(): bool
    {
        return $this->getArticlesLinkedTo()->count() !== $this->articleLinks()->count();
    }

    public function isLive(): bool
    {
        return $this->live_at !== null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public function linkedArticles(?string $url = null): Collection
    {
        $url = $url ?: $this->url();

        return self::query()
            ->withoutGlobalScopes()
            ->where('content', ilike($this->getConnection()), "%$url%")
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    public function liveLinkedArticles(?string $url = null): Collection
    {
        $url = $url ?: $this->url();

        return self::query()
            ->where('content', ilike($this->getConnection()), "%$url%")
            ->get();
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED;
    }

    public function createVersion(): self
    {
        $version = $this->replicate();
        $version->created_at = $this->created_at;
        $version->content = $this->previousContent ?: $this->content;
        if ($this->status === ArticleStatus::PUBLISHED) {
            $this->update([
                'status' => ArticleStatus::DRAFT,
                'live_at' => null,
            ]);
        }

        $this->versions()->save($version);
        $version->topics()->attach($this->topics->pluck('id'));

        return $version;
    }

    public function publish(): void
    {
        $this->status = ArticleStatus::PUBLISHED;
        if ($this->hasInvalidArticleLinks()) {
            throw new \Exception('Cannot publish an article with invalid links');
        }
        $this->live_at = $this->getFirstPublishedTimestamp();
        $this->versions()->update(['live_at' => null]);
        $this->save();
    }

    public function getFirstPublishedTimestamp(): Carbon
    {
        return $this->versions()
            ->where('status', ArticleStatus::PUBLISHED->value)
            ->whereNotNull('live_at')
            ->orderBy('live_at', 'asc')
            ->first()
            ?->live_at ?? now();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('public', function (Builder $query) {
            $query->whereHas('folder', function (Builder $query) {
                $query->whereHas('category', function (Builder $query) {
                    $query->where('is_public', true);
                });
            });
        });
        static::addGlobalScope('published', function (Builder $query) {
            $query->where('status', ArticleStatus::PUBLISHED->value);
        });
        static::addGlobalScope('live', function (Builder $query) {
            $query->whereNotNull('live_at');
        });

        foreach (['saved', 'deleted'] as $event) {
            static::$event(function (self $model) use ($event) {
                cache()->tags(['support', 'articles'])->flush();
                cache()->tags(['support'])->forget(self::cachePrefix().'article:'.$model->friendly_url);

                if ($event === 'saved' && $model->wasChanged('friendly_url')) {
                    $originalUrl = $model->url($model->getOriginal('friendly_url'));
                    $model::withoutEvents(
                        fn () => $model->linkedArticles($originalUrl)
                            ->each(function (self $article) use ($originalUrl, $model) {
                                $article->timestamps = false;
                                $article->update(['content' => str_replace($originalUrl, $model->url(), $article->content)]);
                            })
                    );
                }
            });
        }

        static::saving(function (self $article) {
            if (! $article->friendly_url) {
                throw new \Exception('Cannot create an article without a friendly URL');
            }
            $newStatus = $article->status;
            $isLive = $article->isLive();
            $isPublished = $newStatus === ArticleStatus::PUBLISHED;
            $isBeingPublished = $isPublished && $article->isDirty('published');

            match (true) {
                $article->latest_id !== null && $article->exists => throw new \Exception('Cannot update an article version'),
                $isLive && ! $isPublished => throw new \Exception('An article cannot be live without being published'),
                ! $isLive && $isBeingPublished => throw new \Exception('An article cannot be published without being live'),
                $isLive && SupportArticle::where('friendly_url', $article->friendly_url)
                    ->where('id', '!=', $article->id)
                    ->exists() => throw new \Exception('An article with the same friendly URL already exists'),
                default => null,
            };

            Nova::whenServing(function () use ($article) {
                if (auth()->user()) {
                    $article->edited_by = auth()->user()->name;
                }
            });
            if ($article->exists && $article->isDirty('content')) {
                $article->previousContent = $article->getOriginal('content');
            }
        });
        static::creating(function (self $article) {
            if (! $article->getAttribute('status')) {
                $article->status = ArticleStatus::DRAFT;
            }
        });
        static::deleting(function (self $article) {
            if ($article->isLive() && $article->liveLinkedArticles()->isNotEmpty()) {
                throw new \Exception('Cannot delete article with linked articles');
            }
        });
    }

    /**
     * @param  callable(\Illuminate\Database\Eloquent\Builder<\App\Models\Support\SupportArticle>): \Illuminate\Database\Eloquent\Builder<\App\Models\Support\SupportArticle>|null  $queryDecorator
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    protected static function getCachedFromQuery(string $key, ?callable $queryDecorator = null, array $tags = []): Collection
    {
        $queryDecorator = $queryDecorator ?? fn (Builder $query) => $query;
        $fetchArticles = static fn () => self::fetchArticles($queryDecorator(self::query()));

        return self::getCachedFromCallback(self::cachePrefix().$key, $fetchArticles, $tags);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected static function getCachedFromCallback(string $key, callable $callback, array $tags = [])
    {
        if (! config('hylark.support.cache.enabled')) {
            return $callback();
        }

        return cache()->tags(['support', ...$tags])->remember($key, self::cacheTtl(), fn () => $callback());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Support\SupportArticle>  $query
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>
     */
    protected static function fetchArticles(Builder $query): Collection
    {
        return $query->with('topics')
            ->addSelect('id', 'title', 'friendly_url', 'content', 'views', 'thumbs_up', 'thumbs_down', 'created_at', 'updated_at')
            ->get();
    }

    protected static function cachePrefix(): string
    {
        return config('hylark.support.cache.key').'.articles.';
    }

    protected static function cacheTtl(): int
    {
        return config('hylark.support.cache.ttl');
    }
}
