<?php

declare(strict_types=1);

namespace App\Models\Support;

use Illuminate\Support\Str;
use App\Models\Contracts\NotScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property string $friendly_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 */
class SupportTopic extends Model implements NotScoped
{
    protected $guarded = [];

    protected $touches = ['articles'];

    public function getConnectionName()
    {
        return config('hylark.support.database');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function name(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes = []): array {
            $toSet = ['name' => $value];
            $this->attributes['name'] = $value;

            if (! isset($attributes['friendly_id'])) {
                $toSet['friendly_id'] = Str::slug($value);
                $this->attributes['friendly_id'] = $toSet['friendly_id'];
            }

            return $toSet;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Support\SupportArticle>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(SupportArticle::class, 'support_article_topic', 'topic_id', 'article_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportTopic>
     */
    public static function getCachedTopics(): Collection
    {
        $fetchTopics = static fn () => self::query()->has('articles')->get();

        if (! config('hylark.support.cache.enabled')) {
            return $fetchTopics();
        }

        return cache()->tags(['support', 'topics'])->remember(self::cacheKey(), self::cacheTtl(), fn () => $fetchTopics());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportTopic>
     */
    public static function getCachedPopularTopics(): Collection
    {
        $fetchTopics = static fn () => self::query()
            ->has('articles')
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->take(5)
            ->get();

        if (! config('hylark.support.cache.enabled')) {
            return $fetchTopics();
        }

        return cache()->tags(['support', 'topics'])->remember(self::cacheKey().'.popular', self::cacheTtl(), fn () => $fetchTopics());
    }

    public static function sanitizeTopicIds(?array $topics = []): array
    {
        if ($topics && \is_string($topics[0])) {
            return self::query()->whereIn('friendly_id', $topics)->get()->pluck('id')->all();
        }

        return $topics ?? [];
    }

    protected static function boot()
    {
        parent::boot();

        foreach (['saved', 'deleted'] as $event) {
            static::$event(static function () {
                if (config('hylark.support.cache.enabled')) {
                    cache()->tags(['support', 'topics'])->flush();
                }
            });
        }
    }

    protected static function cacheKey(): string
    {
        return config('hylark.support.cache.key').'.topics';
    }

    protected static function cacheTtl(): int
    {
        return config('hylark.support.cache.ttl');
    }
}
