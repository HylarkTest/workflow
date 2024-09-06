<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Items;

use App\Models\Pin;
use App\Models\Item;
use App\Models\Link;
use App\Models\Note;
use App\Models\Page;
use App\Models\Todo;
use App\Models\Event;
use App\Models\Mapping;
use App\Models\Document;
use App\GraphQL\AppContext;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use GraphQL\Type\Definition\Type;
use Timekeeper\Core\DeadlineStatus;
use LighthouseHelpers\Core\Mutation;
use App\Models\Contracts\FeatureList;
use Lampager\Laravel\PaginationResult;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use LighthouseHelpers\Pagination\Cursor;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Models\Contracts\FeatureListItem;
use Elastic\ScoutDriverPlus\Support\Query;
use App\Core\Mappings\Repositories\ItemFilter;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;
use App\Core\Mappings\Repositories\ItemRepository;
use Mappings\Core\Mappings\Fields\FieldCollection;
use App\GraphQL\Queries\Concerns\BroadcastsChanges;
use LighthouseHelpers\Core\EagerRelationBatchLoader;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

/**
 * @phpstan-import-type RelationFilter from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type ItemFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type MarkerFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type FieldFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 *
 * @template TList of FeatureList
 * @template TItem of FeatureListItem
 */
class ItemQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\BroadcastsChanges<TList, TItem>
     */
    use BroadcastsChanges;

    /**
     * @param  null  $rootValue
     * @param array{
     *     first: int,
     *     after?: string,
     *     type?: string,
     *     hasEmails?: bool,
     *     search?: string,
     *     spaceId?: string,
     *     orderBy?: array,
     *     withFeatures?: string[],
     *     deadlineStage?: string,
     *     mappingId?: string,
     *     due?: bool,
     * } $args
     *
     * @throws \JsonException
     */
    public function indexAll($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): \Lampager\PaginationResult
    {
        $query = Query::bool();

        if (isset($args['spaceId'])) {
            $query->filter(Query::term()->field('space_id')->value($args['spaceId']));
        }

        if (isset($args['type'])) {
            $query->filter(Query::term()->field('type')->value($args['type']));
        }

        if (isset($args['mappingId'])) {
            $query->filter(Query::term()->field('mapping_id')->value($args['mappingId']));
        }

        if ($args['hasEmails'] ?? false) {
            $query->filter(Query::exists()->field('emails'));
        }

        if ($args['withFeatures'] ?? false) {
            $query->filter(Query::terms()->field('features')->values($args['withFeatures']));
        }

        if ($args['deadlineStage'] ?? false) {
            $status = DeadlineStatus::from($args['deadlineStage']);
            $query->must($status->buildEsQuery());
        }

        if ($args['due'] ?? false) {
            $query->mustNot(Query::exists()->field('completed_at'))
                ->must(
                    Query::bool()
                        ->should(Query::exists()->field('start_at'))
                        ->should(Query::exists()->field('due_by'))
                        ->minimumShouldMatch(1)
                );
        }

        if (isset($args['search'])) {
            $query->mustRaw([
                'dis_max' => [
                    'queries' => [
                        [
                            'match_phrase_prefix' => [
                                'name' => [
                                    'query' => $args['search'],
                                ],
                            ],
                        ],
                        [
                            'match' => [
                                'name' => [
                                    'query' => $args['search'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        } else {
            $query->must(Query::matchAll());
        }

        $builder = Item::searchQuery($query);

        $order = $args['orderBy'] ?? [['field' => 'id', 'direction' => 'DESC']];
        if (! collect($order)->contains('field', 'id')) {
            $order[] = ['field' => 'id', 'direction' => 'desc'];
        }
        foreach ($order as $orderByClause) {
            $field = match ($orderByClause['field']) {
                'match' => '_score',
                'mapping' => 'mapping_id',
                default => Str::snake($orderByClause['field']),
            };
            $builder->sort(Str::snake($field), $orderByClause['direction']);
        }

        $builder->size($args['first'] + 1);

        if ($args['after'] ?? null) {
            $builder->searchAfter(Cursor::decode(['after' => $args['after']]));
        }

        $results = $builder->execute();

        $count = $results->hits()->count();

        $hits = $results->hits();

        return new PaginationResult(
            $results->models()
                ->slice(0, $args['first'])
                ->each(fn (Model $model, $index) => $model->setAttribute('cursor', $hits->get($index)->sort())),
            [
                'total' => $results->total(),
                'hasNextPage' => $count === $args['first'] + 1,
                'hasPreviousPage' => (bool) ($args['after'] ?? false),
                'nextCursor' => $hits->get($count - 2)?->sort(),
            ]
        );
    }

    /**
     * @param array{
     *     first: int,
     *     after?: string,
     *     forRelation?: array{
     *        itemId: string,
     *        relationId: string,
     *     },
     *     filters?: ItemFilterCollection,
     *     markers?: MarkerFilterCollection,
     *     fields?: FieldFilterCollection,
     *     orderBy?: OrderBy,
     *     group?: string,
     *     includeGroups?: string[],
     *     excludeGroups?: string[],
     * } $args
     *
     * @throws \JsonException
     */
    public function index(Mapping $mapping, array $args, AppContext $context, ResolveInfo $resolveInfo): \Lampager\PaginationResult|array|SyncPromise
    {
        $filter = (new ItemFilter)->mapping($mapping);

        $forRelation = $args['forRelation'] ?? null;
        if ($forRelation) {
            $forRelation['itemId'] = $this->decodeId($forRelation['itemId'], 'Item');
            $filter->relation($forRelation);
        }

        if ($args['filters'] ?? false) {
            $filter->filters($args['filters']);
        }

        if ($args['markers'] ?? false) {
            $filter->markers($args['markers']);
        }

        if ($args['fields'] ?? false) {
            $filter->fields($args['fields']);
        }

        if ($args['includeGroups'] ?? false) {
            $filter->includeGroups($args['includeGroups']);
        }
        if ($args['excludeGroups'] ?? false) {
            $filter->includeGroups($args['excludeGroups']);
        }

        $repository = resolve(ItemRepository::class);

        return $repository->paginateItems(
            /** @phpstan-ignore-next-line Fetching the necessary params */
            Arr::only($args, ['first', 'after']),
            $filter,
            $args['orderBy'] ?? [],
            $args['group'] ?? null,
        );
    }

    /**
     * @throws \JsonException
     */
    public function show(Mapping $mapping, array $args, AppContext $context, ResolveInfo $resolveInfo): Item
    {
        /** @var \App\Models\Item $item */
        $item = $mapping->items()->findOrFail($this->decodeId($args['id'], 'Item'));

        return $item;
    }

    public function store(Mapping $mapping, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        if (! $base->accountLimits()->canCreateEntities()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $this->trim($args);
        $this->validate(
            $args,
            [
                ...$this->rules($mapping, true, $args),
                'input.startAt' => 'nullable|date',
                'input.dueBy' => 'nullable|date',
            ],
            $resolveInfo,
            $this->messages($mapping),
            $this->attributes($mapping),
        );

        $input = $this->buildInputFields($args['input'], $mapping->fields);

        $globalId = resolve(GlobalId::class);

        /** @var array<int, array{0: int[], 1: array{context: string}}> $markerAttaches */
        $markerAttaches = [];
        if (isset($args['input']['markers'])) {
            foreach ($args['input']['markers'] as $markerInfo) {
                /** @var \App\Core\Mappings\Markers\MappingMarkerGroup|null $mappingMarkerGroup */
                $mappingMarkerGroup = $mapping->markerGroups?->find($markerInfo['context']);
                [$type, $groupId] = $globalId->decode($markerInfo['groupId']);
                if (! $mappingMarkerGroup || $mappingMarkerGroup->relationship || $type !== 'MarkerGroup' || $mappingMarkerGroup->group !== (int) $groupId) {
                    continue;
                }
                /** @var \App\Models\MarkerGroup $markerGroup */
                $markerGroup = MarkerGroup::query()->findOrFail($mappingMarkerGroup->group);
                /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marker> $markers */
                $markers = $markerGroup->markers()->findOrFail(array_map(fn ($markerId) => $globalId->decodeID($markerId), $markerInfo['markers']));
                $markerAttaches[] = [$markers->modelKeys(), ['context' => $mappingMarkerGroup->id()]];
            }
        }

        /** @var \App\Models\Item $item */
        $item = Item::withoutSyncingToSearch(function () use ($mapping, $input, $markerAttaches) {
            /** @var \App\Models\Item $item */
            $item = $mapping->items()->create($input);

            foreach ($markerAttaches as [$markerIds, $pivotData]) {
                /** @var array{context: string} $pivotData */
                $item->markers()->attach($markerIds, $pivotData);
            }
            $item->unsetRelation('markers');

            return $item;
        });

        $item->instantSearchable();

        $response = $this->mutationResponse(200, "The $mapping->singular_name was created successfully", [
            $mapping->graphql_single_field => $item,
        ]);

        $this->broadcastResponse("items.$mapping->graphql_many_field.{$mapping->graphql_single_field}Created", $response);

        return $response;
    }

    public function update(Mapping $mapping, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $id = $this->decodeId($args['input']['id'], 'Item');
        /** @var \App\Models\Item $item */
        $item = $mapping->items()->find($id);

        $this->trim($args);
        $this->validate(
            $args,
            $this->rules($mapping, false, $args),
            $resolveInfo,
            $this->messages($mapping),
            $this->attributes($mapping),
        );

        $input = $this->buildInputFields($args['input'], $mapping->fields, $item->data);

        if (isset($args['input']['isFavorite'])) {
            $input['favorited_at'] = $args['input']['isFavorite'] ? now() : null;
        }
        $extraFields = [
            'priority',
            'startAt',
            'dueBy',
        ];
        foreach ($extraFields as $field) {
            if (isset($args['input'][$field])) {
                if ($field === 'dueBy' && isset($args['input']['startAt'])) {
                    $this->validate($args, ['input.dueBy' => ['after:input.startAt']], $resolveInfo);
                }
                $input[$field] = $args['input'][$field];
            }
        }
        if (isset($args['input']['isCompleted'])) {
            $input['completed_at'] = $args['input']['isCompleted'] ? now() : null;
        }

        $item->updateAndSyncWithSearch($input);

        $this->broadcastItemChange($item);

        return $this->mutationResponse(200, "The $mapping->singular_name was updated successfully", [
            $mapping->graphql_single_field => $item,
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function duplicate(Mapping $mapping, array $args, AppContext $context): array
    {
        $base = $context->base();

        if (! $base->accountLimits()->canCreateEntities()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        /** @var \App\Models\Item $item */
        $item = Item::withoutSyncingToSearch(function () use ($mapping, $args) {
            $id = $this->decodeId($args['input']['id'], 'Item');

            /** @var \App\Models\Item $originalItem */
            $originalItem = $mapping->items()->findOrFail($id);

            return resolve(ItemRepository::class)
                ->duplicateItem($originalItem, $args['input']);
        });

        $item->instantSearchable();

        $response = $this->mutationResponse(200, "The $mapping->singular_name was created successfully", [
            $mapping->graphql_single_field => $item,
        ]);

        $this->broadcastResponse("items.$mapping->graphql_many_field.{$mapping->graphql_single_field}Created", $response);

        return $response;
    }

    public function destroy(Mapping $mapping, array $args, AppContext $context): array
    {
        $query = $mapping->items();
        $shouldForce = $args['input']['force'] ?? false;
        if ($shouldForce) {
            /** @phpstan-ignore-next-line Items is soft deletable */
            $query->withTrashed();
        }
        /** @var \App\Models\Item $item */
        $item = $query->findOrFail($this->decodeId($args['input']['id'], 'Item'));

        Item::instantSync(function () use ($item, $shouldForce) {
            if ($shouldForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        $this->broadcastItemDeleted($item);

        return $this->mutationResponse(204, "The $mapping->singular_name was deleted successfully");
    }

    /**
     * @param  \App\Models\Contracts\FeatureListItem<TList, TItem>&\Illuminate\Database\Eloquent\Model  $node
     */
    public function resolveAssociations(Model&FeatureListItem $node, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();
        $typeFromNode = MappingFeatureType::featureForNode($node);
        /** @phpstan-ignore-next-line what the heck PHPStan, this is right! */
        $mappingsWithFeature = $base->mappings->filter(fn (Mapping $mapping) => $mapping->featureEnabled($typeFromNode));

        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($resolveInfo->path, function () use ($mappingsWithFeature) {
            return new RelationBatchLoader(
                new SimpleModelsLoader(
                    'items',
                    fn (MorphToMany $builder) => $builder->whereIn('mapping_id', $mappingsWithFeature->modelKeys())
                )
            );
        });

        return $instance->load($node);
    }

    /**
     * @param  null  $rootValue
     */
    public function associateItem($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($args['input']['itemId']);
        $mapping = $item->mapping;

        $node = Utils::resolveModelFromGlobalId($args['input']['associatableId']);

        /** @phpstan-ignore-next-line It doesn't let me define the type properly */
        $this->validateAssociatable($mapping, $node);

        /** @phpstan-ignore-next-line  */
        $node->items()->syncWithoutDetaching($item);

        $this->broadcastChanges($item);
        $this->broadcastChanges($node);

        return $this->mutationResponse(200, 'The item was set successfully', [
            'item' => $item,
            'node' => $node,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function removeItem($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($args['input']['itemId']);
        $mapping = $item->mapping;

        $node = Utils::resolveModelFromGlobalId($args['input']['associatableId']);

        /** @phpstan-ignore-next-line It doesn't let me define the type properly */
        $this->validateAssociatable($mapping, $node);

        /** @phpstan-ignore-next-line  */
        $node->items()->detach($item);

        $this->broadcastChanges($item);
        $this->broadcastChanges($node);

        return $this->mutationResponse(200, 'The item was set successfully', [
            'item' => $item,
            'node' => $node,
        ]);
    }

    public function resolvePages(Item $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $path = $resolveInfo->path;
        array_pop($path);

        return BatchLoaderRegistry::instance(
            [...$path, 'markers|pages'],
            fn () => new EagerRelationBatchLoader(['pages', 'markers'])
        )->load($rootValue)->then(function ($rootValue) {
            return $rootValue->pages->filter(function (Page $page) use ($rootValue) {
                return $rootValue->existsInPage($page);
            });
        });
    }

    public function messages(Mapping $mapping): array
    {
        return $this->mapThroughFields($mapping, fn (Field $field) => $field->messages());
    }

    public function attributes(Mapping $mapping): array
    {
        return $this->mapThroughFields($mapping, fn (Field $field) => $field->attributes());
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     *
     * @phpstan-ignore-next-line Not sure how to define this
     */
    protected function validateAssociatable(Mapping $mapping, FeatureListItem|Item $associatable, bool $throw = true): bool
    {
        if ($associatable instanceof Item) {
            return true;
        }
        /** @phpstan-ignore-next-line Not sure why it can't see the list property */
        $allowedType = $associatable->list->space_id === $mapping->space_id && match (true) {
            $associatable instanceof Todo => $mapping->featureEnabled(MappingFeatureType::TODOS),
            $associatable instanceof Event => $mapping->featureEnabled(MappingFeatureType::EVENTS),
            $associatable instanceof Document => $mapping->featureEnabled(MappingFeatureType::DOCUMENTS),
            $associatable instanceof Note => $mapping->featureEnabled(MappingFeatureType::NOTES),
            $associatable instanceof Link => $mapping->featureEnabled(MappingFeatureType::LINKS),
            $associatable instanceof Pin => $mapping->featureEnabled(MappingFeatureType::PINBOARD),
            default => false,
        };

        if (! $allowedType) {
            if ($throw) {
                $this->throwValidationException('input.associatableId', ['The associatable ID cannot be linked to the specified item']);
            }

            return false;
        }

        return true;
    }

    protected function buildInputFields(array $input, FieldCollection $fields, ?array $originalData = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $input['data'] ?? [];
        $input['data'] = $fields->mapWithKeys(static function (Field $field) use ($data, $originalData) {
            $originalValue = Arr::get($originalData ?: [], $field->id());
            if (\array_key_exists($field->apiName, $data)) {
                return [$field->id() => $field->serializeValue($data[$field->apiName], $originalValue)];
            }

            return [$field->id() => $originalValue];
        })->filter(fn ($data) => filled($data))->all();

        return $input;
    }

    protected function rules(Mapping $mapping, bool $isCreate, array $args): array
    {
        return $this->mapThroughFields($mapping, function (Field $field) use ($args, $isCreate) {
            $rules = $field->rules($isCreate);

            $fieldName = $field->fieldName();

            foreach ($rules as $key => $ruleSet) {
                if (\is_array($ruleSet)) {
                    $newRuleSet = [];
                    foreach ($ruleSet as $rule) {
                        if (\is_string($rule) && str_contains($rule, '{field}')) {
                            if ($field->isList()) {
                                $submittedValues = Arr::get($args, "input.data.$fieldName.listValue", []);
                                foreach ($submittedValues as $index => $value) {
                                    $valueKey = str_replace('*', (string) $index, $key);
                                    if (! isset($rules[$valueKey])) {
                                        $rules[$valueKey] = [];
                                    }
                                    $rules[$valueKey][] = str_replace('{field}', "input.data.$fieldName.listValue.$index.fieldValue", $rule);
                                }
                            } else {
                                $newRuleSet[] = str_replace('{field}', "input.data.$fieldName.fieldValue", $rule);
                            }
                        } else {
                            $newRuleSet[] = $rule;
                        }
                    }
                    $rules[$key] = $newRuleSet;
                }
            }

            return $rules;
        });
    }

    protected function mapThroughFields(Mapping $mapping, \Closure $callback): array
    {
        $results = [];
        $mapping->fields->each(function (Field $field) use ($callback, &$results) {
            $fieldItems = $callback($field);

            $fieldName = $field->fieldName();

            foreach ($fieldItems as $key => $item) {
                if (\is_int($key)) {
                    $results['input.data.'.$fieldName] = $item;
                } else {
                    $results['input.data.'.$fieldName.'.'.$key] = $item;
                }
            }
        });

        return $results;
    }

    protected function trim(array &$args): void
    {
        // Recursively trim all strings in the array
        array_walk_recursive($args, static function (&$value) {
            if (\is_string($value)) {
                $value = trim($value);
            }
        });
    }
}
