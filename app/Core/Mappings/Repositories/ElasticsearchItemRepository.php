<?php

declare(strict_types=1);

namespace App\Core\Mappings\Repositories;

use App\Models\Item;
use App\Models\Marker;
use App\Models\Mapping;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Support\Carbon;
use Mappings\Models\CategoryItem;
use Illuminate\Support\Collection;
use Lampager\Laravel\PaginationResult;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Pagination\Cursor;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Core\Mappings\FieldFilterOperator;
use Elastic\Client\ClientBuilderInterface;
use Elastic\ScoutDriverPlus\Support\Query;
use App\Core\Mappings\MarkerFilterOperator;
use Mappings\Core\Mappings\Fields\FieldType;
use App\GraphQL\Queries\Concerns\GroupsQueries;
use LighthouseHelpers\Exceptions\ValidationException;
use Elastic\ScoutDriverPlus\Builders\BoolQueryBuilder;
use Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface;
use Mappings\Core\Mappings\Fields\Contracts\CustomSortable;

/**
 * @phpstan-import-type ItemFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type ItemFilterOptions from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type FieldFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type MarkerFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 */
class ElasticsearchItemRepository
{
    /** @use \App\GraphQL\Queries\Concerns\GroupsQueries<\App\Models\Item> */
    use GroupsQueries;

    protected ?Mapping $mapping = null;

    protected ?ItemFilter $filter = null;

    /**
     * @param  PaginationArgs  $paginationArgs
     */
    public function readPage(
        array $paginationArgs,
        ?ItemFilter $filter = null,
        array $order = [],
        ?string $group = null,
    ): PaginationResult|array {
        if ($filter?->getMapping()) {
            $this->mapping = $filter->getMapping();
        }
        $this->filter = $filter;
        $query = $this->getRawBuilder($filter);

        /** @var int $rawTotal */
        $rawTotal = Item::searchQuery($query)->size(0)->execute()->total();

        if ($group) {
            return $this->buildGroupedPaginator($group, $paginationArgs, $rawTotal, $order, $filter);
        }

        $query = $this->applyFiltersToQuery($query, $filter);

        return $this->buildPaginator($query, $paginationArgs, $rawTotal, $order);
    }

    protected function getRawBuilder(?ItemFilter $filter): BoolQueryBuilder
    {
        return tap(Query::bool(), function (BoolQueryBuilder $query) use ($filter) {
            if ($mapping = $filter?->getMapping()) {
                $query->filter(Query::term()->field('mapping_id')->value($mapping->id));
            }
            if ($relation = $filter?->getRelation()) {
                $query->filter(Query::nested()
                    ->path('parentRelations')
                    ->query(
                        Query::bool()
                            ->filter(Query::term()->field('parentRelations.relation_id')->value($relation['relationId']))
                            ->filter(Query::term()->field('parentRelations.item_id')->value($relation['itemId']))
                    ));
            }
            if ($fields = $filter?->getFields()) {
                // We already established this has to be there
                /** @var \App\Models\Mapping $mapping */
                $mapping = $this->mapping;
                foreach ($this->buildFieldQueries($fields, $mapping) as $fieldQuery) {
                    $query->must($fieldQuery);
                }
            }
            if ($markers = $filter?->getMarkers()) {
                foreach ($this->buildMarkerQueries($markers) as $markerQuery) {
                    $query->must($markerQuery);
                }
            }
        });
    }

    /**
     * @param  ItemFilterCollection|ItemFilterOptions  $filters
     */
    protected function buildRecursiveQuery(BoolQueryBuilder $query, array $filters): BoolQueryBuilder
    {
        if (! array_is_list($filters)) {
            $filters = [$filters];
        }
        /** @var ItemFilterCollection $filters */
        foreach ($filters as $filter) {
            $method = match ($filter['boolean']) {
                'AND' => 'must',
                'OR' => 'should',
            };
            /** @phpstan-ignore-next-line PHPStan doesn't like recursive types */
            if ($filter['filters'] ?? false) {
                /** @var ItemFilterCollection $filters */
                $filters = $filter['filters'];
                foreach ($filters as $subFilter) {
                    $query->$method($this->buildRecursiveQuery(Query::bool(), $subFilter));
                }
            }
            if ($filter['fields'] ?? false) {
                // We already established this has to be there
                /** @var \App\Models\Mapping $mapping */
                $mapping = $this->mapping;
                foreach ($this->buildFieldQueries($filter['fields'], $mapping) as $fieldQuery) {
                    $query->$method($fieldQuery);
                }
            }
            if ($filter['markers'] ?? false) {
                foreach ($this->buildMarkerQueries($filter['markers']) as $markerQuery) {
                    $query->$method($markerQuery);
                }
            }
            if (isset($filter['isFavorited'])) {
                $query->$method($this->filterBuilderForFavorites(Query::bool(), $filter['isFavorited']));
            }
            if (isset($filter['priority'])) {
                $query->$method($this->filterBuilderForPriority(Query::bool(), (string) $filter['priority']));
            }
            if ($filter['search'] ?? false) {
                foreach ($filter['search'] as $search) {
                    $query->$method(
                        Query::bool()->mustRaw([[
                            'dis_max' => [
                                'queries' => [
                                    [
                                        'match_phrase_prefix' => [
                                            'name' => [
                                                'query' => $search,
                                            ],
                                        ],
                                    ],
                                    [
                                        'match' => [
                                            'name' => [
                                                'query' => $search,
                                                'fuzziness' => 'AUTO',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]])
                    );
                }
            }
            if ($method === 'should') {
                $query->minimumShouldMatch(1);
            }
        }

        return $query;
    }

    protected function applyFiltersToQuery(BoolQueryBuilder $query, ?ItemFilter $filter): BoolQueryBuilder
    {
        $filters = $filter?->getFilters();
        if ($filters) {
            $advancedFilter = $this->buildRecursiveQuery(Query::bool(), $filters);
            $query->filter($advancedFilter);
        }

        return $query;
    }

    /**
     * @param  PaginationArgs  $paginationArgs
     * @param  OrderBy  $order
     *
     * @throws \JsonException
     */
    protected function buildPaginator(BoolQueryBuilder $builder, array $paginationArgs, int $rawTotal, array $order): PaginationResult
    {
        $query = Item::searchQuery($builder);
        if (! collect($order)->contains('field', 'id')) {
            $order[] = ['field' => 'id', 'direction' => empty($order) ? 'desc' : Arr::last($order)['direction']];
        }
        $sort = [];
        foreach ($order as $orderByClause) {
            $orderField = $orderByClause['field'];
            if (Str::startsWith($orderField, 'field:')) {
                $id = mb_substr($orderField, 6);
                $this->validateMapping();
                /** @var \App\Models\Mapping $mapping */
                $mapping = $this->mapping;
                /** @var \Mappings\Core\Mappings\Fields\Field $field */
                $field = $mapping->fields->find($id);
                $sort[] = $this->getFieldSortQuery($field, $orderByClause['direction']);
            } else {
                if ($orderField === 'NAME') {
                    $orderField = 'name.keyword';
                }
                $sort[] = [
                    Str::snake(mb_strtolower($orderField)) => mb_strtolower($orderByClause['direction']),
                ];
            }
        }
        $query->sortRaw($sort);

        $first = $paginationArgs['first'];
        $after = $paginationArgs['after'] ?? null;

        $query->size($first + 1);

        if ($after) {
            $query->searchAfter(Cursor::decode(['after' => $after]));
        }

        $results = $query->execute();

        $count = $results->hits()->count();

        $hits = $results->hits();

        return new PaginationResult(
            $results->models()
                ->slice(0, $first)
                ->each(fn (Model $model, $index) => $model->setAttribute('cursor', $hits->get($index)->sort())),
            [
                'total' => $results->total(),
                'rawTotal' => $rawTotal,
                'hasNext' => $count === $first + 1,
                'hasPrevious' => (bool) $after,
                'nextCursor' => $hits->get($count - 2)?->sort(),
            ]
        );
    }

    protected function validateMapping(): void
    {
        if (! $this->mapping) {
            throw ValidationException::withMessages(['mapping' => 'The mapping must be set to perform this query.']);
        }
    }

    protected function getFieldSortQuery(Field $field, string $direction): array
    {
        $typeMap = Item::esFieldTypeMap();
        $type = null;
        foreach ($typeMap as $esType => $enums) {
            if (Arr::first($enums, fn ($enum) => $enum->is($field->type()))) {
                $type = $esType;
                break;
            }
        }
        if (! $field->canBeSorted()) {
            throw ValidationException::withMessages(['orderBy' => 'Cannot sort by the selected field.']);
        }
        if ($field instanceof CustomSortable) {
            $key = "$type.sortable_value";
        } else {
            $key = "$type.value";
        }
        if ($type === 'text_fields') {
            $key .= '.keyword';
        }

        return [$key => [
            'order' => mb_strtolower($direction),
            'nested' => [
                'path' => $type,
                'filter' => Query::term()->field("$type.field")->value($field->id())->buildQuery(),
            ],
        ]];
    }

    /**
     * @param  PaginationArgs  $paginationArgs
     */
    protected function buildGroupedPaginator(
        string $group,
        array $paginationArgs,
        int $rawTotal,
        array $orderBy,
        ?ItemFilter $filter = null,
    ): array {
        $groups = $this->fetchGroups($group, [
            'includeGroups' => $filter?->getIncludeGroups() ?: [],
            'excludeGroups' => $filter?->getExcludeGroups() ?: [],
        ]);

        return [
            'groups' => $groups->map(function ($groupHeader) use ($group, $paginationArgs, $rawTotal, $orderBy, $filter) {
                $builder = $this->getRawBuilder($filter);
                $this->applyFiltersToQuery($builder, $filter);
                $groupQuery = $this->filterBuilderForGroup($builder, $group, $groupHeader);
                $paginator = $this->buildPaginator($groupQuery, $paginationArgs, $rawTotal, $orderBy);
                $paginator->groupHeader = $this->getGroupHeaderId($group, $groupHeader);
                if ($paginator->groupHeader !== $groupHeader) {
                    $paginator->group = $groupHeader;
                } else {
                    $paginator->group = null;
                }

                return $paginator;
            })->all(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function getFieldHeaders(string $fieldId): Collection
    {
        if (! isset($this->mapping)) {
            $this->throwInvalidGroup();
        }
        $field = $this->mapping->fields->find($fieldId);
        if (! $field || $field->option('isRange', false)) {
            $this->throwInvalidGroup();
        }

        return match ($field->type()->value) {
            /** @phpstan-ignore-next-line Need the null value */
            FieldType::SELECT()->value => collect($field->option('valueOptions'))->keys()->map(fn ($value) => (string) $value)->push(null),
            FieldType::BOOLEAN()->value => collect(['1', '0']),
            /** @phpstan-ignore-next-line We know it's a rating field */
            FieldType::RATING()->value => /** @var \Mappings\Core\Mappings\Fields\Types\RatingField $field */ collect(range(0, $field->max()))->map(fn ($value) => (string) $value),
            FieldType::CATEGORY()->value => /** @var \Mappings\Core\Mappings\Fields\Types\CategoryField $field */ $this->getUsedCategoryHeaders($field),
            FieldType::CURRENCY()->value => /** @var \Mappings\Core\Mappings\Fields\Types\CategoryField $field */ $this->getUsedCurrencyHeaders($field),
            FieldType::DATE()->value => /** @var \Mappings\Core\Mappings\Fields\Types\DateField $field */ $this->getUsedDateHeaders($field),
            default => $this->throwInvalidGroup(),
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Marker|null>
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     *
     * Overrides trait method because the marker group id could be from the mapping
     */
    protected function getMarkerHeaders(string $id): Collection
    {
        $mappingMarkerGroup = $this->mapping?->markerGroups?->find($id);
        $markerGroup = $mappingMarkerGroup
            ? $mappingMarkerGroup->markerGroup()
            : Utils::resolveModelFromGlobalId($id);

        if (! $markerGroup instanceof MarkerGroup) {
            $this->throwInvalidGroup();
        }

        // Need to clone the collection as it gets cached on the model with
        // octane.
        /** @phpstan-ignore-next-line We are adding a null value for the null group */
        return (clone $markerGroup->markers)->push(null);
    }

    protected function itemsExistMissingField(Field $field): bool
    {
        $builder = $this->getRawBuilder($this->filter);
        $this->applyFiltersToQuery($builder, $this->filter);
        $nullItems = Item::searchQuery($builder->filter(
            Query::bool()->mustNot(Query::nested()->path('keyword_fields')->query(Query::term()->field('keyword_fields.field')->value($field->id())))
        ))->size(0)->execute()->total();

        return $nullItems > 0;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int|string|null>
     *
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function getFieldBuckets(Field $field, bool $withNull = true): Collection
    {
        $esClient = resolve(ClientBuilderInterface::class)->default();
        $builder = $this->getRawBuilder($this->filter);
        $this->applyFiltersToQuery($builder, $this->filter);
        $path = match ($field->type()->value) {
            FieldType::SELECT()->value, FieldType::CATEGORY()->value, FieldType::CURRENCY()->value => 'keyword_fields',
            FieldType::DATE()->value => 'date_fields',
            default => $this->throwInvalidGroup(),
        };
        /*
         * Check out this link to see how this query works:
         * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html
         */
        /** @var \Elastic\Elasticsearch\Response\Elasticsearch $response */
        $response = $esClient->search([
            'index' => (new Item)->searchableAs(),
            'routing' => tenant()->id,
            'body' => [
                'size' => 0,
                'query' => $builder->buildQuery(),
                'aggs' => [
                    'keyword_fields' => [
                        'nested' => [
                            'path' => $path,
                        ],
                        'aggs' => [
                            'fields' => [
                                'filter' => Query::bool()->filter(
                                    Query::term()->field("$path.field")->value($field->id)
                                )->buildQuery(),
                                'aggs' => [
                                    'field' => [
                                        'terms' => [
                                            'field' => "$path.value",
                                            'size' => 100,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $results = $response->asArray();
        /** @var array{key: string, doc_count: int} $buckets */
        $buckets = $results['aggregations']['keyword_fields']['fields']['field']['buckets'];

        $headers = collect($buckets)->pluck('key');

        if ($withNull) {
            if ($this->itemsExistMissingField($field)) {
                $headers->push(null);
            }
        }

        return $headers;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Mappings\Models\CategoryItem|null>
     *
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function getUsedCategoryHeaders(Field $field): Collection
    {
        $buckets = $this->getFieldBuckets($field, false);

        $headers = CategoryItem::query()->findMany($buckets);

        if ($this->itemsExistMissingField($field)) {
            /** @phpstan-ignore-next-line Need the null header */
            $headers->push(null);
        }

        /** @phpstan-ignore-next-line The null value is throwing it off */
        return $headers;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string|null>
     *
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function getUsedCurrencyHeaders(Field $field): Collection
    {
        /** @var \Illuminate\Support\Collection<int, string|null> $buckets */
        $buckets = $this->getFieldBuckets($field);

        return $buckets;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string|null>
     *
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function getUsedDateHeaders(Field $field): Collection
    {
        /** @var \Illuminate\Support\Collection<int, int|null> $buckets */
        $buckets = $this->getFieldBuckets($field);

        return $buckets
            ->sortBy(fn (?int $timestamp) => $timestamp ?? INF)
            ->map(fn (?int $timestamp) => $timestamp ? Carbon::createFromTimestampMs($timestamp)->toDateString() : null);
    }

    protected function filterBuilderForGroup(BoolQueryBuilder $query, string $group, mixed $groupHeader): BoolQueryBuilder
    {
        [$group, $id] = $this->explodeGroup($group);

        $method = 'filterBuilderFor'.Str::studly($group);
        if (method_exists($this, $method)) {
            return $this->$method($query, $groupHeader, $id);
        }

        return $query->filter(Query::term()->field(mb_strtolower($group))->value($groupHeader));
    }

    protected function filterBuilderForField(BoolQueryBuilder $builder, null|string|CategoryItem $groupHeader, string $fieldId): BoolQueryBuilder
    {
        /** @var \Mappings\Core\Mappings\Fields\Field $field */
        /** @phpstan-ignore-next-line Mapping must exist here */
        $field = $this->mapping->fields->find($fieldId);
        if ($groupHeader === null) {
            /** @var string $path */
            $path = match ($field->type()->value) {
                FieldType::SELECT()->value, FieldType::CATEGORY()->value, FieldType::CURRENCY()->value => 'keyword_fields',
                FieldType::DATE()->value => 'date_fields',
                default => $this->throwInvalidGroup(),
            };

            return $builder->filter(Query::bool()->mustNot(Query::nested()->path($path)->query(Query::term()->field("$path.field")->value($fieldId))));
        }
        if ($field->type()->is(FieldType::BOOLEAN())) {
            $value = match ($groupHeader) {
                '0' => false,
                '1' => true,
                default => null,
            };
        } elseif ($groupHeader instanceof CategoryItem) {
            $value = $groupHeader->id;
        } else {
            $value = $groupHeader;
        }
        $fieldQuery = $this->buildQueryForField($field, FieldFilterOperator::IS, $value);
        if (! $fieldQuery) {
            $this->throwInvalidGroup();
        }

        return $builder->filter($fieldQuery);
    }

    protected function filterBuilderForMarker(BoolQueryBuilder $query, ?Marker $groupHeader, string $id): BoolQueryBuilder
    {
        $mappingMarkerGroup = $this->mapping?->markerGroups?->find($id);

        if (! $groupHeader) {
            $groupId = $mappingMarkerGroup
                ? $mappingMarkerGroup->group
                : $this->getGlobalId()->decodeId($id);
            $nestedQuery = Query::bool()->must(
                Query::term()->field('markers.marker_group_id')->value($groupId)
            );

            if ($mappingMarkerGroup) {
                $nestedQuery->must(
                    Query::term()->field('markers.context')->value($mappingMarkerGroup->id())
                );
            }

            return $query->filter(
                Query::bool()->mustNot(
                    Query::nested()
                        ->path('markers')
                        ->query($nestedQuery)
                )
            );
        }

        $nestedQuery = Query::bool()->must(
            Query::term()->field('markers.id')->value($groupHeader->id)
        );

        if ($mappingMarkerGroup) {
            $nestedQuery->must(
                Query::term()->field('markers.context')->value($mappingMarkerGroup->id())
            );
        }

        return $query->filter(
            Query::nested()
                ->path('markers')
                ->query($nestedQuery)
        );
    }

    protected function filterBuilderForFavorites(BoolQueryBuilder $query, mixed $groupHeader): BoolQueryBuilder
    {
        $subQuery = Query::exists()->field('favorited_at');
        if ($groupHeader) {
            $query->filter(Query::bool()->must($subQuery));
        } else {
            $query->filter(Query::bool()->mustNot($subQuery));
        }

        return $query;
    }

    protected function filterBuilderForPriority(BoolQueryBuilder $query, mixed $groupHeader): BoolQueryBuilder
    {
        if ($groupHeader === '0') {
            return $query->filter(
                Query::bool()->should(Query::bool()->mustNot(Query::exists()->field('priority')))
                    ->should(Query::term()->field('priority')->value(0))
                    ->minimumShouldMatch(1)
            );
        }

        return $query->filter(Query::term()->field('priority')->value($groupHeader));
    }

    protected function buildQueryForField(Field $field, FieldFilterOperator $operator, mixed $match): ?QueryBuilderInterface
    {
        /** @var \Mappings\Core\Mappings\Fields\FieldType[] $map */
        $map = Item::esFieldTypeMap();
        $type = $field->type();
        $esPath = null;
        foreach ($map as $path => $types) {
            /** @phpstan-ignore-next-line This is the correct format */
            if (collect($types)->some(fn (FieldType $fieldType) => $fieldType->is($type))) {
                $esPath = $path;
                break;
            }
        }

        if (! $esPath) {
            return null;
        }

        return match ($esPath) {
            'text_fields' => $this->buildTextQuery($field, $operator, $match),
            'date_fields' => $this->buildDateQuery($field, $operator, $match),
            'keyword_fields' => $this->buildKeywordQuery($field, $operator, $match),
            'boolean_fields' => $this->buildBooleanQuery($field, $operator, $match),
            default => null,
        };
    }

    protected function buildNestedQuery(string $path, Field $field, QueryBuilderInterface $valueQuery, FieldFilterOperator $operator): QueryBuilderInterface
    {
        $query = Query::nested()
            ->path($path)
            ->query(
                Query::bool()
                    ->filter(Query::term()->field("$path.field")->value($field->id()))
                    ->filter($valueQuery)
            );

        return match ($operator) {
            FieldFilterOperator::IS => $query,
            FieldFilterOperator::IS_NOT => Query::bool()->mustNot($query),
        };
    }

    protected function buildTextQuery(Field $field, FieldFilterOperator $operator, mixed $match): QueryBuilderInterface
    {
        $query = Query::term()->field('text_fields.value.keyword')->value($match);

        return $this->buildNestedQuery('text_fields', $field, $query, $operator);
    }

    protected function buildDateQuery(Field $field, FieldFilterOperator $operator, mixed $match): QueryBuilderInterface
    {
        $query = Query::term()->field('date_fields.value')->value($match);

        return $this->buildNestedQuery('date_fields', $field, $query, $operator);
    }

    protected function buildKeywordQuery(Field $field, FieldFilterOperator $operator, mixed $match): QueryBuilderInterface
    {
        if ($match === '0' && $field->type()->is(FieldType::RATING())) {
            $query = Query::bool()
                ->should(
                    Query::bool()->mustNot(Query::nested()->path('keyword_fields')->query(Query::term()->field('keyword_fields.field')->value($field->id())))
                )
                ->should(
                    Query::nested()
                        ->path('keyword_fields')
                        ->query(
                            Query::bool()
                                ->filter(Query::term()->field('keyword_fields.field')->value($field->id()))
                                ->filter(Query::term()->field('keyword_fields.value')->value($match))
                        )
                )
                ->minimumShouldMatch(1);

            return match ($operator) {
                FieldFilterOperator::IS => $query,
                FieldFilterOperator::IS_NOT => Query::bool()->mustNot($query),
            };
        }

        $query = Query::term()->field('keyword_fields.value')->value($match);

        return $this->buildNestedQuery('keyword_fields', $field, $query, $operator);
    }

    protected function buildBooleanQuery(Field $field, FieldFilterOperator $operator, mixed $match): QueryBuilderInterface
    {
        if ($match === false) {
            $match = true;
            $operator = match ($operator) {
                FieldFilterOperator::IS => FieldFilterOperator::IS_NOT,
                FieldFilterOperator::IS_NOT => FieldFilterOperator::IS,
            };
        }

        $query = Query::term()->field('boolean_fields.value')->value($match);

        return $this->buildNestedQuery('boolean_fields', $field, $query, $operator);
    }

    /**
     * @param  FieldFilterCollection  $fields
     * @return array<int, \Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface>
     */
    protected function buildFieldQueries(array $fields, Mapping $mapping): array
    {
        return collect($fields)
            ->map(function ($filter) use ($mapping) {
                /** @var \Mappings\Core\Mappings\Fields\Field $field */
                $field = $mapping->fields->find($filter['fieldId']);

                $operator = $filter['operator'];
                if (\is_string($operator)) {
                    $operator = FieldFilterOperator::from($operator);
                }

                return $this->buildQueryForField(
                    $field,
                    $operator,
                    $filter['match']
                );
            })->filter()->all();
    }

    /**
     * @param  MarkerFilterCollection  $markers
     * @return array<int, \Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface>
     */
    protected function buildMarkerQueries(array $markers): array
    {
        $globalId = resolve(GlobalId::class);

        return collect($markers)
            ->map(function ($filter) use ($globalId) {
                $bool = Query::bool()
                    ->filter(Query::term()->field('markers.id')->value($globalId->decodeID($filter['markerId'])));

                if ($filter['context'] ?? false) {
                    $bool->filter(Query::term()->field('markers.context')->value($filter['context']));
                }

                $query = Query::nested()->path('markers')
                    ->query($bool);

                $operator = $filter['operator'];
                if (\is_string($operator)) {
                    $operator = MarkerFilterOperator::from($operator);
                }

                return match ($operator) {
                    MarkerFilterOperator::IS => $query,
                    MarkerFilterOperator::IS_NOT => Query::bool()->mustNot($query),
                };
            })->all();
    }
}
