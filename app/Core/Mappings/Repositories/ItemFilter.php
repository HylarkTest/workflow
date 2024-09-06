<?php

declare(strict_types=1);

namespace App\Core\Mappings\Repositories;

use App\Models\Mapping;

/**
 * @phpstan-type ItemFilterOptions array{boolean: 'AND'|'OR', fields?: FieldFilterCollection, markers?: MarkerFilterCollection, search?: string[], isFavorited?: bool, priority?: int}
 * @phpstan-type ItemFilterCollection array<int, ItemFilterOptions>
 * @phpstan-type FieldFilter array{fieldId: string, operator: 'IS'|'IS_NOT'|\App\Core\Mappings\FieldFilterOperator, match: mixed}
 * @phpstan-type FieldFilterCollection array<int, FieldFilter>
 * @phpstan-type MarkerFilter array{markerId: string, operator: 'IS'|'IS_NOT'|\App\Core\Mappings\MarkerFilterOperator, context?: string}
 * @phpstan-type MarkerFilterCollection array<int, MarkerFilter>
 * @phpstan-type RelationFilter array{itemId: int, relationId: string}
 */
class ItemFilter
{
    protected Mapping $mapping;

    /**
     * @var RelationFilter
     */
    protected array $relation;

    protected string $search;

    /**
     * @var ItemFilterCollection
     */
    protected array $filters;

    /**
     * @var MarkerFilterCollection
     */
    protected array $markers;

    /**
     * @var FieldFilterCollection
     */
    protected array $fields;

    /**
     * @var string[]
     */
    protected array $includeGroups;

    /**
     * @var string[]
     */
    protected array $excludeGroups;

    public function mapping(Mapping $mapping): self
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @param  RelationFilter  $relation
     * @return $this
     */
    public function relation(array $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function search(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @param  ItemFilterCollection  $filters
     * @return $this
     */
    public function filters(array $filters): self
    {
        if (! isset($this->mapping)) {
            throw new \Exception('Mapping must be set before setting filters');
        }
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param  MarkerFilterCollection  $markers
     * @return $this
     *
     * @throws \Exception
     */
    public function markers(array $markers): self
    {
        if (! isset($this->mapping)) {
            throw new \Exception('Mapping must be set before setting markers');
        }
        $this->markers = $markers;

        return $this;
    }

    /**
     * @param  FieldFilterCollection  $fields
     * @return $this
     *
     * @throws \Exception
     */
    public function fields(array $fields): self
    {
        if (! isset($this->mapping)) {
            throw new \Exception('Mapping must be set before setting fields');
        }
        $this->fields = $fields;

        return $this;
    }

    public function includeGroups(array $groups): self
    {
        $this->includeGroups = $groups;

        return $this;
    }

    public function excludeGroups(array $groups): self
    {
        $this->excludeGroups = $groups;

        return $this;
    }

    public function getMapping(): ?Mapping
    {
        return $this->mapping ?? null;
    }

    /**
     * @return RelationFilter
     */
    public function getRelation(): ?array
    {
        return $this->relation ?? null;
    }

    /**
     * @return ItemFilterCollection
     */
    public function getFilters(): array
    {
        return $this->filters ?? [];
    }

    public function getMarkers(): array
    {
        return $this->markers ?? [];
    }

    public function getFields(): array
    {
        return $this->fields ?? [];
    }

    public function getIncludeGroups(): array
    {
        return $this->includeGroups ?? [];
    }

    public function getExcludeGroups(): array
    {
        return $this->excludeGroups ?? [];
    }
}
