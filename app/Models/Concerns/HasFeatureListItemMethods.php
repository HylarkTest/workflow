<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use GraphQL\Deferred;
use Finder\CanBeGloballySearched;
use Actions\Models\Concerns\HasActions;
use LighthouseHelpers\Concerns\HasGlobalId;
use LighthouseHelpers\Core\ModelBatchLoader;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

/**
 * @template TList of \App\Models\Contracts\FeatureList
 *
 * @implements \App\Models\Contracts\FeatureListItem<TList>
 */
trait HasFeatureListItemMethods
{
    use AdvancedSoftDeletes;
    use CanBeAssigned;
    use CanBeGloballySearched;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasBaseScopedRelationships;
    use HasFactory;
    use HasGlobalId;
    use HasMarkers;
    use RelatedToOtherModels;

    public function toGloballySearchableArray(): array
    {
        return [
            'id' => $this->id,
            'space_id' => $this->list->space_id,
            'primary' => [
                'text' => $this->getSearchableName(),
                'map' => 'name',
            ],
            'secondary' => collect([
                [
                    'text' => $this->list->name,
                    'map' => $this->list()->getRelationName().'.name',
                ],
            ])->merge($this->secondarySearchableArray())
                ->filter(fn (?array $item) => $item && $item['text'])->values()->all(),
        ];
    }

    public function getSearchableName(): string
    {
        return $this->name;
    }

    public function globallySearchableWith(): array
    {
        return ['list:id,space_id,name'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<int, int>
     */
    public function listId(): Attribute
    {
        return new Attribute(
            get: fn () => $this->{$this->list()->getForeignKeyName()},
            set: fn ($value) => $this->{$this->list()->getForeignKeyName()} = $value
        );
    }

    public static function formatListIdActionPayload(?int $listId): ?Deferred
    {
        return $listId ? ModelBatchLoader::instanceFromModel(
            \get_class((new self)->list()->getRelated()),
            true
        )->loadAndResolve(
            $listId, [],
            fn ($list): string => $list?->name ?? '['.trans('common.unknown').']'
        ) : null;
    }

    public static function formatOrderActionPayload(): null
    {
        return null;
    }

    protected function secondarySearchableArray(): array
    {
        return $this->getAssigneesMappedForFinder();
    }
}
