<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\ExternalTodoable;
use App\Models\ExternalEventable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Item> $items
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Todo> $todos
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Document> $documents
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Event> $events
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Pin> $pins
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Link> $links
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Note> $notes
 *
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Item> items()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Todo> todos()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Document> documents()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Event> events()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Pin> pins()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Link> links()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Note> notes()
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait RelatedToOtherModels
{
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @template TRelated of \Illuminate\Database\Eloquent\Model
     *
     * @param  TModel  $root
     * @param  class-string<TRelated>  $foreign
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelated>
     */
    public static function getRelationBetween($root, string $foreign): MorphToMany
    {
        $rootClass = $root::class;
        foreach (static::getRelationshipMap() as [$parent, $child, $pivot]) {
            if ($rootClass === $parent && $foreign === $child) {
                /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelated> $relation */
                $relation = $root->morphedByMany($foreign, $pivot);

                return self::formatRelation($relation);
            }
            if ($rootClass === $child && $foreign === $parent) {
                /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelated> $relation */
                $relation = $root->morphToMany($foreign, $pivot);

                return self::formatRelation($relation);
            }
        }
        throw new \RuntimeException("The two models $rootClass and $foreign could not be related");
    }

    public static function bootRelatedToOtherModels(): void
    {
        foreach (static::getRelationshipMap() as [$parent, $child, $pivot]) {
            if ($parent === __CLASS__) {
                static::resolveRelationUsing(
                    (new $child)->getTable(),
                    fn (self $root) => self::formatRelation($root->morphedByMany($child, $pivot))
                );
            }
            if ($child === __CLASS__) {
                static::resolveRelationUsing(
                    (new $parent)->getTable(),
                    fn (self $root) => self::formatRelation($root->morphToMany($parent, $pivot))
                );
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\ExternalEventable>
     */
    public function externalEventables(): MorphMany
    {
        return $this->morphMany(ExternalEventable::class, 'eventable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\ExternalTodoable>
     */
    public function externalTodoables(): MorphMany
    {
        return $this->morphMany(ExternalTodoable::class, 'todoable');
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphToMany<TModel>  $relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<TModel>
     */
    protected static function formatRelation(MorphToMany $relation): MorphToMany
    {
        return $relation->withPivot('created_at as added_at')
            ->withTimestamps();
    }

    /**
     * @return array<int, array{0: class-string<\Illuminate\Database\Eloquent\Model>, 1: class-string<\Illuminate\Database\Eloquent\Model>, 2: string}>
     */
    protected static function getRelationshipMap(): array
    {
        return config('hylark.relationship_map', []);
    }
}
