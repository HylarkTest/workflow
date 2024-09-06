<?php

declare(strict_types=1);

namespace App\GraphQL\Utils;

use App\Models\Item;
use App\Models\Mapping;
use Illuminate\Support\Collection;
use App\GraphQL\Queries\MarkerQuery;
use Illuminate\Pagination\Paginator;
use Nuwave\Lighthouse\Support\Utils;
use App\GraphQL\Queries\HistoryQuery;
use App\GraphQL\Queries\ImportsQuery;
use LighthouseHelpers\Core\AddsTypes;
use App\GraphQL\Queries\AssigningQuery;
use App\GraphQL\Queries\Items\ItemQuery;
use App\GraphQL\Queries\Features\PinQuery;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use App\GraphQL\Queries\Features\LinkQuery;
use App\GraphQL\Queries\Features\NoteQuery;
use App\GraphQL\Queries\Features\TodoQuery;
use App\GraphQL\Queries\Features\EmailQuery;
use App\GraphQL\Queries\Features\EventQuery;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use App\GraphQL\Queries\Features\DocumentQuery;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Features\ExternalTodoQuery;
use App\GraphQL\Subscriptions\BaseItemSubscription;
use App\GraphQL\Queries\Features\ExternalEventQuery;
use App\GraphQL\Queries\Items\ItemRelationshipQuery;
use Mappings\Core\Mappings\Relationships\Relationship;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Pagination\SimplePaginatorField;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Subscriptions\SubscriptionRegistry;
use GraphQL\Type\Definition\ResolveInfo as BaseResolveInfo;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;
use Nuwave\Lighthouse\Subscriptions\Exceptions\UnauthorizedSubscriber;

/**
 * This is where the magic happens.
 * Here we take the mappings that have been defined by the user and generate a
 * schema that Lighthouse can parse and connect to the code.
 *
 * @phpstan-import-type FieldDefinition from \LighthouseHelpers\Core\AddsTypes
 */
class MappingTypeBuilder
{
    use AddsTypes;

    public function __construct(
        protected TypeRegistry $registry
    ) {}

    public function getRegistry(): \LighthouseHelpers\Core\TypeRegistry
    {
        /** @phpstan-ignore-next-line */
        return $this->registry;
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function registerDynamicTypes(Mapping $mapping): void
    {
        $type = $mapping->graphql_type;
        $manyField = $mapping->graphql_many_field;
        $pluralType = ucfirst($manyField);
        $singleField = $mapping->graphql_single_field;

        $controllerClass = ItemQuery::class;

        $extraInputFields = [];
        $extraItemFields = [];

        if ($mapping->featureEnabled(MappingFeatureType::FAVORITES)) {
            $extraInputFields['isFavorite'] = $this->boolean(nullable: true);
            $extraItemFields['isFavorite'] = $this->boolean(resolver: fn (Item $root) => $root->isFavorite());
        }
        if ($mapping->featureEnabled(MappingFeatureType::PRIORITIES)) {
            $extraInputFields['priority'] = $this->int(nullable: true);
            $extraItemFields['priority'] = $this->int(resolver: fn (Item $root) => $root->getPriority());
        }
        if ($mapping->featureEnabled(MappingFeatureType::TIMEKEEPER)) {
            $extraInputFields['startAt'] = $this->dateTime(nullable: true);
            $extraInputFields['dueBy'] = $this->dateTime(nullable: true);
            $extraInputFields['isCompleted'] = $this->boolean(nullable: true);
        }

        $mapping->fields->registerDynamicTypes($type);

        $this->registerLazyObject("{$type}ItemData", fn () => [
            'fields' => $mapping->fields->graphQLData($type)->toArray(),
        ]);

        $this->registerLazyInput("{$type}ItemDataInput", fn () => [
            'fields' => $mapping->fields->graphQLInputFields($type)->toArray(),
        ]);

        $this->registerLazyInput("{$type}ItemCreateInput", fn () => [
            'fields' => [
                'data' => $this->buildInputType("{$type}ItemDataInput", nullable: true),
                'markers' => $this->buildInputType('MarkersInput', list: true, nullable: true),
            ],
        ]);

        $this->registerLazyInput("{$type}ItemUpdateInput", fn () => [
            'fields' => [
                'id' => $this->id('Item'),
                'data' => $this->buildInputType("{$type}ItemDataInput", nullable: true),
                ...$extraInputFields,
            ],
        ]);

        $this->registerLazyInput("{$type}ItemDuplicateInput", fn () => [
            'fields' => [
                'id' => $this->id('Item'),
                ...$this->getRecordFields(),
            ],
        ]);

        $this->registerLazyInput("{$type}ItemDeleteInput", fn () => [
            'fields' => [
                'id' => $this->id('Item'),
                'force' => $this->boolean(default: false),
            ],
        ]);

        $this->registerLazyObject("{$type}ItemPreview", fn () => [
            'name' => "{$type}ItemPreview",
            'fields' => fn () => [
                'id' => $this->id('Item'),
                'spaceId' => $this->id('Space', resolver: fn ($item) => $item->mapping->space_id),
                'name' => $this->string(resolver: fn (Item $item) => $item->resolvePrimaryName()),
                'image' => $this->buildType('ItemImage', nullable: true, resolver: fn (Item $item) => $item->resolvePrimaryImage()),
                'names' => $this->buildType('StringFieldValue', resolver: fn (Item $item) => $item->resolveNameFieldValues(), list: true),
                'images' => $this->buildType('ItemImageFieldValue', list: true, resolver: fn (Item $item) => $item->resolveImageFieldValues()),
                'emails' => $this->email(resolver: fn (Item $item) => $item->resolveAllEmails(), list: true, nullable: true),
                'data' => $this->buildType("{$type}ItemData", nullable: true, resolver: fn ($item) => $item->resolveItemDataAttributes()),
                'errors' => $this->buildType('PreviewFieldErrors', list: true),
                'mapping' => $this->buildType(
                    'Mapping',
                    resolver: fn ($item, $args, $context, $resolveInfo) => $item->relationLoaded('mapping') ? $item->mapping : BatchLoaderRegistry::instance(
                        $resolveInfo->path,
                        static fn () => new RelationBatchLoader(new SimpleModelsLoader('mapping', fn () => null)),
                    )->load($item),
                ),
                ...$extraItemFields,
                'createdAt' => $this->dateTime(),
                'updatedAt' => $this->dateTime(),
            ],
        ]);

        $this->registerLazyObject("{$type}Item", fn () => [
            'name' => "{$type}Item",
            'interfaces' => [
                $this->getInterface('Node'),
                $this->getInterface('Item'),
                $this->getInterface('Findable'),
                $this->getInterface('Markable'),
                $this->getInterface('Assignable'),
                $this->getInterface('ActionSubject'),
                $this->getInterface('FetchesActions'),
            ],
            'fields' => fn () => [
                'id' => $this->id('Item'),
                'spaceId' => $this->id('Space', resolver: fn ($item) => $item->mapping->space_id),
                'name' => $this->string(resolver: fn (Item $item) => $item->resolvePrimaryName()),
                'image' => $this->buildType('ItemImage', nullable: true, resolver: fn (Item $item) => $item->resolvePrimaryImage()),
                'names' => $this->buildType('StringFieldValue', resolver: fn (Item $item) => $item->resolveNameFieldValues(), list: true),
                'images' => $this->buildType('ItemImageFieldValue', list: true, resolver: fn (Item $item) => $item->resolveImageFieldValues()),
                'emails' => $this->email(resolver: fn (Item $item) => $item->resolveAllEmails(), list: true, nullable: true),
                'data' => $this->buildType("{$type}ItemData", nullable: true, resolver: fn ($item) => $item->resolveItemDataAttributes()),
                'mapping' => $this->buildType(
                    'Mapping',
                    resolver: fn ($item, $args, $context, $resolveInfo) => BatchLoaderRegistry::instance(
                        $resolveInfo->path,
                        static fn () => new RelationBatchLoader(new SimpleModelsLoader('mapping', fn () => null)),
                    )->load($item),
                ),
                'pages' => $this->buildType(
                    'ItemPage',
                    list: true,
                    resolver: Utils::constructResolver($controllerClass, 'resolvePages'),
                ),
                'markerGroups' => $this->buildType(
                    'MarkerCollection',
                    list: true,
                    nullable: true,
                    resolver: Utils::constructResolver(MarkerQuery::class, 'resolveCollection'),
                ),
                'assigneeGroups' => $this->buildType(
                    'AssigneeInfo',
                    list: true,
                    resolver: Utils::constructResolver(AssigningQuery::class, 'resolveAssignees'),
                ),
                'deadlines' => $this->buildType(
                    'DeadlineInfo',
                    resolver: fn ($item) => $item->getDeadlineInfo(),
                ),
                ...$extraItemFields,
                'createdAt' => $this->dateTime(),
                'updatedAt' => $this->dateTime(),
                'createAction' => $this->buildType(
                    'Action',
                    nullable: true,
                    resolver: Utils::constructResolver(HistoryQuery::class, 'resolveCreateAction'),
                ),
                'latestAction' => $this->buildType(
                    'Action',
                    nullable: true,
                    resolver: Utils::constructResolver(HistoryQuery::class, 'resolveLatestAction'),
                ),
            ],
        ]);

        $this->registerLazyObject("{$type}ItemMutationResponse", fn () => [
            'interfaces' => [
                $this->getInterface('MutationResponse'),
            ],
            'fields' => [
                'code' => $this->string(nullable: true),
                'success' => $this->boolean(nullable: true),
                'message' => $this->string(nullable: true),
                $singleField => $this->buildType("{$type}Item", nullable: true),
            ],
        ]);

        $this->registerLazyObject("{$type}ItemPreviewResponse", fn () => [
            'interfaces' => [
                $this->getInterface('MutationResponse'),
            ],
            'fields' => [
                'code' => $this->string(nullable: true),
                'success' => $this->boolean(nullable: true),
                'message' => $this->string(nullable: true),
                $manyField => $this->buildType(
                    "{$type}ItemPreviewPaginator",
                    args: [
                        'first' => $this->int(default: 25),
                        'page' => $this->int(default: 1),
                    ],
                ),
            ],
        ]);

        $this->registerLazyObject("{$type}ItemEdge", fn () => [
            'fields' => [
                'node' => $this->buildType("{$type}Item"),
                'cursor' => $this->string(),
            ],
        ]);

        $this->registerConnection("{$type}Item");
        // Cannot use the helper method because the data can be nullable for failed imports
        $this->registerLazyObject("{$type}ItemPreviewPaginator", fn () => [
            'fields' => [
                'data' => $this->buildType(
                    "{$type}ItemPreview",
                    list: true,
                    nullableList: true,
                    resolver: \LighthouseHelpers\Utils::constructResolver(SimplePaginatorField::class, 'dataResolver'),
                ),
                'pageInfo' => $this->simplePageInfo(),
                'errors' => $this->buildType(
                    'PreviewRecordErrors',
                    list: true,
                    resolver: function (Paginator $paginator, $args, $contextInfo, BaseResolveInfo $resolveInfo) {
                        $path = $resolveInfo->path;
                        array_splice($path, -1, 1, 'data');

                        return array_map(function (array $errorInfo) use ($path) {
                            return [
                                ...$errorInfo,
                                'path' => [...$path, ...$errorInfo['path']],
                            ];
                            /** @phpstan-ignore-next-line We added the property */
                        }, $paginator->errors);
                    }
                ),
            ],
        ]);

        $buildItemResolver = static function (string $method) use ($controllerClass, $mapping) {
            $resolver = Utils::constructResolver($controllerClass, $method);

            return static fn () => $resolver($mapping, ...\array_slice(\func_get_args(), 1));
        };

        $this->extendType('ItemQuery', fn () => [
            $singleField => fn () => $this->buildType(
                "{$type}Item",
                resolver: $buildItemResolver('show'),
                args: ['id' => $this->id('Item')]
            ),
            $manyField => fn () => $this->buildType(
                "{$type}ItemConnection",
                resolver: $buildItemResolver('index'),
                args: [
                    'forRelation' => $this->buildType('RelationQueryInput', nullable: true),
                    'filters' => $this->buildType('ItemFilterInput', list: true, nullable: true),
                    'fields' => $this->buildType('FieldFilterInput', list: true, nullable: true),
                    'markers' => $this->buildType('MarkerFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('OrderByClause', list: true, nullable: true),
                    'first' => $this->int(default: 25),
                    'after' => $this->string(nullable: true),
                ],
            ),
        ]);

        /*
         * We need to be careful with naming here. The dynamic type names need to
         * not be subsets/supersets of other dynamic typenames. For example, if
         * If the user had types 'Foo' and 'FooGrouped' and we had the connection
         * and grouped connection type suffixes as 'ItemConnection' and
         * 'GroupedItemConnection', then this builder would create the types:
         * - FooItemConnection
         * - FooGroupedItemConnection
         * - FooGroupedItemConnection
         * - FooGroupedGroupedItemConnection
         * And we would have a clash. By using the suffixes 'ItemConnection' and
         * 'ItemGroupedConnection', we get:
         * - FooItemConnection
         * - FooItemGroupedConnection
         * - FooGroupedItemConnection
         * - FooGroupedItemGroupedConnection
         * Which is fine.
         */

        $this->registerLazyObject("{$type}ItemGroupedConnection", fn () => [
            'fields' => [
                'groupHeader' => $this->string(nullable: true),
                'group' => $this->buildType('Groupable', nullable: true),
                'edges' => $this->edges("{$type}ItemEdge"),
                'pageInfo' => $this->pageInfo(),
            ],
        ]);
        $this->registerLazyObject("{$type}ItemGrouped", fn () => [
            'fields' => [
                'groups' => $this->buildType("{$type}ItemGroupedConnection", list: true),
            ],
        ]);
        $this->extendType('GroupedItemQuery', fn () => [
            $manyField => fn () => $this->buildType(
                "{$type}ItemGrouped",
                resolver: $buildItemResolver('index'),
                args: [
                    'group' => $this->string(),
                    'includeGroups' => $this->string(list: true, nullable: true, nullableList: true),
                    'excludeGroups' => $this->string(list: true, nullable: true, nullableList: true),
                    'forRelation' => $this->buildType('RelationQueryInput', nullable: true),
                    'filters' => $this->buildType('ItemFilterInput', list: true, nullable: true),
                    'fields' => $this->buildType('FieldFilterInput', list: true, nullable: true),
                    'markers' => $this->buildType('MarkerFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('OrderByClause', list: true, nullable: true),
                    'first' => $this->int(default: 25),
                    'after' => $this->string(nullable: true),
                ],
            ),
        ]);

        $this->registerLazyObject("{$type}ItemMutation", fn () => [
            'fields' => [
                "create$type" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    resolver: $buildItemResolver('store'),
                    args: ['input' => $this->buildType("{$type}ItemCreateInput")],
                ),
                "update$type" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    resolver: $buildItemResolver('update'),
                    args: ['input' => $this->buildType("{$type}ItemUpdateInput")],
                ),
                "duplicate$type" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    resolver: $buildItemResolver('duplicate'),
                    args: ['input' => $this->buildType("{$type}ItemDuplicateInput")],
                ),
                "delete$type" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    resolver: $buildItemResolver('destroy'),
                    args: ['input' => $this->buildType("{$type}ItemDeleteInput")],
                ),
                "preview$pluralType" => fn () => $this->buildType(
                    "{$type}ItemPreviewResponse",
                    resolver: function () use ($mapping) {
                        $resolver = Utils::constructResolver(ImportsQuery::class, 'prepareFileForPreview');

                        return $resolver($mapping, ...\array_slice(\func_get_args(), 1));
                    },
                    args: ['input' => $this->buildType('PreviewSpreadsheetInput')],
                ),
            ],
        ]);

        $this->extendType('ItemMutation', fn () => [
            $manyField => fn () => $this->buildType("{$type}ItemMutation", resolver: $this->rootResolver()),
        ]);

        $this->registerLazyObject("{$type}ItemSubscription", fn () => [
            'fields' => [
                "{$singleField}Created" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    nullable: true,
                    resolver: $this->provideSubscriptionResolver("items.$manyField.{$singleField}Created"),
                ),
                "{$singleField}Updated" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    nullable: true,
                    resolver: $this->provideSubscriptionResolver("items.$manyField.{$singleField}Updated"),
                ),
                "{$singleField}Deleted" => fn () => $this->buildType(
                    "{$type}ItemMutationResponse",
                    nullable: true,
                    resolver: $this->provideSubscriptionResolver("items.$manyField.{$singleField}Deleted"),
                ),
            ],
        ]);

        $this->extendType('ItemSubscription', fn () => [
            $manyField => fn () => $this->buildType("{$type}ItemSubscription", resolver: $this->rootResolver()),
        ]);

        $this->buildRelationshipTypes($mapping);
        $this->buildFeatureTypes($mapping);
        $this->buildMarkerTypes($mapping);
    }

    /**
     * @return FieldDefinition
     */
    protected function markerGroupQueryDefinition(MappingMarkerGroup $markerGroup): array
    {
        return $this->buildType(
            'Marker',
            list: ! $markerGroup->isSingle(),
            nullable: true,
            resolver: function (Item $root, array $args, $context, BaseResolveInfo $resolveInfo) use ($markerGroup) {
                $resolver = Utils::constructResolver(MarkerQuery::class, 'index');
                $args['markerGroup'] = $markerGroup;

                return $resolver($root, $args, $context, $resolveInfo);
            },
        );
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    protected function buildMarkerTypes(Mapping $mapping): void
    {
        $markers = $mapping->markerGroups;

        if ($markers && $markers->isNotEmpty()) {
            $partitions = $markers->partition(fn (MappingMarkerGroup $markerGroup) => $markerGroup->relationship !== null);
            /** @var \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup> $relationshipGroups */
            /** @var \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup> $normalGroups */
            [$relationshipGroups, $normalGroups] = $partitions;

            $this->buildRelationshipMarkerTypes($relationshipGroups, $mapping);
            $this->buildNormalMarkerTypes($normalGroups, $mapping);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup>  $relationMarkerGroups
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    protected function buildRelationshipMarkerTypes(Collection $relationMarkerGroups, Mapping $mapping): void
    {
        $markerQueryFields = $relationMarkerGroups->whereNotNull('relationship')->mapWithKeys(function (MappingMarkerGroup $markerGroup) {
            return [$markerGroup->apiName => fn () => $this->markerGroupQueryDefinition($markerGroup)];
        });
        $relationMarkerGroups->pluck('relationship')
            ->unique('id')
            ->each(function (Relationship $relationship) use ($mapping, $markerQueryFields) {
                $typePrefix = $this->relationshipTypePrefix($relationship, $mapping);

                $this->registerLazyObject("{$typePrefix}Markers", fn () => [
                    'fields' => $markerQueryFields->toArray(),
                ]);
                $this->extendType("{$typePrefix}Edge", [
                    'markers' => fn () => $this->buildType("{$typePrefix}Markers", nullable: true),
                ]);
            });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup>  $markerGroups
     */
    protected function buildNormalMarkerTypes(Collection $markerGroups, Mapping $mapping): void
    {
        if ($markerGroups->isNotEmpty()) {
            $mappingType = $mapping->graphql_type;

            $queryTypes = $markerGroups->mapWithKeys(
                fn (MappingMarkerGroup $options) => [$options->apiName => fn () => $this->markerGroupQueryDefinition($options)]
            );

            $this->registerLazyObject("{$mappingType}ItemMarkers", fn () => [
                'fields' => $queryTypes->all(),
            ]);

            $this->extendType("{$mappingType}Item", fn () => [
                'markers' => fn () => $this->buildType("{$mappingType}ItemMarkers", resolver: $this->rootResolver()),
            ]);
        }
    }

    protected function buildFeatureTypes(Mapping $mapping): void
    {
        $mappingType = $mapping->graphql_type;

        $paginationArgs = [
            'first' => $this->int(default: 25),
            'after' => $this->string(nullable: true),
        ];

        $fields = collect([
            'todos' => fn () => $this->buildType(
                'TodoConnection',
                resolver: Utils::constructResolver(TodoQuery::class, 'index'),
                args: [
                    'filters' => $this->buildType('TodoFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('TodoOrderBy', list: true, nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'externalTodos' => fn () => $this->buildType(
                'ExternalTodoPaginator',
                resolver: Utils::constructResolver(ExternalTodoQuery::class, 'index'),
                args: [
                    'dueBefore' => $this->dateTime(nullable: true),
                    'dueAfter' => $this->dateTime(nullable: true),
                    'filter' => $this->buildType('ExternalTodoFilter', default: 'ALL'),
                    'first' => $this->int(default: 25),
                    'page' => $this->int(nullable: true),
                ],
            ),
            'events' => fn () => $this->buildType(
                'EventConnection',
                resolver: Utils::constructResolver(EventQuery::class, 'index'),
                args: [
                    'orderBy' => $this->buildType('EventOrderBy', list: true, nullable: true),
                    'includeRecurringInstances' => $this->boolean(default: false),
                    'startsBefore' => $this->dateTime(nullable: true),
                    'startsAfter' => $this->dateTime(nullable: true),
                    'endsBefore' => $this->dateTime(nullable: true),
                    'endsAfter' => $this->dateTime(nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'externalEvents' => fn () => $this->buildType(
                'ExternalEventPaginator',
                resolver: Utils::constructResolver(ExternalEventQuery::class, 'index'),
                args: [
                    'startsBefore' => $this->dateTime(nullable: true),
                    'endsAfter' => $this->dateTime(nullable: true),
                    'first' => $this->int(default: 25),
                    'page' => $this->int(nullable: true),
                ],
            ),
            'notes' => fn () => $this->buildType(
                'NoteConnection',
                resolver: Utils::constructResolver(NoteQuery::class, 'index'),
                args: [
                    'filters' => $this->buildType('NoteFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('NoteOrderBy', list: true, nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'pins' => fn () => $this->buildType(
                'PinConnection',
                resolver: Utils::constructResolver(PinQuery::class, 'index'),
                args: [
                    'filters' => $this->buildType('PinFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('PinOrderBy', list: true, nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'documents' => fn () => $this->buildType(
                'DocumentConnection',
                resolver: Utils::constructResolver(DocumentQuery::class, 'index'),
                args: [
                    'filters' => $this->buildType('DocumentFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('DocumentOrderBy', list: true, nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'links' => fn () => $this->buildType(
                'LinkConnection',
                resolver: Utils::constructResolver(LinkQuery::class, 'index'),
                args: [
                    'filters' => $this->buildType('LinkFilterInput', list: true, nullable: true),
                    'orderBy' => $this->buildType('LinkOrderBy', list: true, nullable: true),
                    ...$paginationArgs,
                ],
            ),
            'emails' => fn () => $this->buildType(
                'EmailConnection',
                resolver: Utils::constructResolver(EmailQuery::class, 'index'),
                args: [
                    'first' => $this->int(default: 25),
                    'page' => $this->string(nullable: true),
                ],
            ),
            'emailAssociations' => fn () => $this->buildType(
                'EmailAssociations',
                resolver: $this->rootResolver(),
            ),
        ])->filter(function ($_, $field) use ($mapping) {
            if ($field === 'emailAssociations') {
                return $mapping->featureEnabled(MappingFeatureType::EMAILS);
            }
            if ($field === 'externalTodos') {
                return $mapping->featureEnabled(MappingFeatureType::TODOS);
            }
            if ($field === 'externalEvents') {
                return $mapping->featureEnabled(MappingFeatureType::EVENTS);
            }
            $feature = match ($field) {
                'pins' => MappingFeatureType::PINBOARD,
                'documents' => MappingFeatureType::DOCUMENTS,
                default => MappingFeatureType::from(mb_strtoupper($field)),
            };

            return $mapping->featureEnabled($feature);
        });

        if ($fields->isNotEmpty()) {
            $this->registerLazyObject("{$mappingType}ItemFeatures", fn () => [
                'fields' => $fields->all(),
            ]);
            $this->extendType("{$mappingType}Item", fn () => [
                'features' => $this->buildType("{$mappingType}ItemFeatures", resolver: $this->rootResolver()),
            ]);
        }
    }

    protected function buildRelationshipTypes(Mapping $mapping): void
    {
        $relationships = $mapping->relationshipsWithMappings();
        if ($relationships->isNotEmpty()) {
            $mappingType = $mapping->graphql_type;
            $relationshipFields = $relationships->mapWithKeys(
                fn (Relationship $relationship) => [$relationship->apiName => fn () => $this->relationshipQueryDefinition($relationship, $mapping)]
            );

            $relationships->toBase()->each(function (Relationship $relationship) use ($mapping) {
                $this->relationshipQueryTypes($relationship, $mapping);
                $this->relationMutationDefinition($relationship, $mapping);
            });

            $this->registerLazyObject("{$mappingType}Relations", fn () => [
                'fields' => $relationshipFields->all(),
            ]);

            $this->extendType("{$mappingType}Item", fn () => [
                'relations' => fn () => $this->buildType("{$mappingType}Relations", resolver: $this->rootResolver()),
            ]);
        }
    }

    protected function relationshipTypePrefix(Relationship $relationship, Mapping $mapping): string
    {
        $apiName = ucfirst($relationship->apiName);

        return "$mapping->graphql_type{$apiName}Relation";
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    protected function relationshipQueryDefinition(Relationship $relationship, Mapping $mapping): array
    {
        $typePrefix = $this->relationshipTypePrefix($relationship, $mapping);
        $controllerClass = ItemRelationshipQuery::class;

        if ($relationship->isToMany()) {
            return $this->buildType(
                "{$typePrefix}Connection",
                resolver: Utils::constructResolver($controllerClass, 'connectionResolver'),
                args: [
                    'first' => $this->int(default: 25),
                    'after' => $this->string(nullable: true),
                ],
            );
        }

        return $this->buildType(
            "{$typePrefix}Edge",
            resolver: Utils::constructResolver($controllerClass, 'singleEdgeResolver'),
            nullable: true,
        );
    }

    protected function relationshipQueryTypes(Relationship $relationship, Mapping $mapping): void
    {
        $typePrefix = $this->relationshipTypePrefix($relationship, $mapping);
        /** @var \App\Models\Mapping $mapping */
        $mapping = $relationship->toMapping();
        $toType = $mapping->graphql_type.'Item';

        $edgeFields = function () use ($toType, $relationship) {
            $fields = [
                'node' => $this->buildType($toType),
            ];
            if ($relationship->isToMany()) {
                $fields['cursor'] = $this->string();
            }

            return $fields;
        };

        $this->registerLazyObject("{$typePrefix}Edge", fn () => [
            'fields' => $edgeFields,
        ]);

        if ($relationship->isToMany()) {
            $controllerClass = ItemRelationshipQuery::class;
            $this->registerLazyObject("{$typePrefix}Connection", fn () => [
                'fields' => [
                    'edges' => $this->buildType(
                        "{$typePrefix}Edge",
                        list: true,
                        resolver: Utils::constructResolver($controllerClass, 'edgeResolver'),
                    ),
                    'pageInfo' => $this->pageInfo(),
                ],
            ]);
        }
    }

    protected function relationMutationDefinition(Relationship $relationship, Mapping $mapping): void
    {
        $mappingType = $mapping->graphql_type;
        $apiName = ucfirst($relationship->apiName);
        $controllerClass = ItemRelationshipQuery::class;

        if ($relationship->isToMany()) {
            $this->extendType("{$mappingType}ItemMutation", fn () => [
                "addTo{$apiName}Relationship" => fn () => $this->buildType(
                    "{$mappingType}ItemMutationResponse",
                    resolver: Utils::constructResolver($controllerClass, 'store'),
                    args: ['input' => $this->buildType('AddManyRelationshipsInput')],
                ),
                "removeFrom{$apiName}Relationship" => fn () => $this->buildType(
                    "{$mappingType}ItemMutationResponse",
                    resolver: Utils::constructResolver($controllerClass, 'destroy'),
                    args: ['input' => $this->buildType('RemoveManyRelationshipsInput')],
                ),
            ]);
        } else {
            $this->extendType("{$mappingType}ItemMutation", fn () => [
                "set{$apiName}Relationship" => fn () => $this->buildType(
                    "{$mappingType}ItemMutationResponse",
                    resolver: Utils::constructResolver($controllerClass, 'update'),
                    args: ['input' => $this->buildType('AddSingleRelationshipInput')],
                ),
                "remove{$apiName}Relationship" => fn () => $this->buildType(
                    "{$mappingType}ItemMutationResponse",
                    resolver: Utils::constructResolver($controllerClass, 'destroy'),
                    args: ['input' => $this->buildType('RemoveSingleRelationshipInput')],
                ),
            ]);
        }
    }

    protected function provideSubscriptionResolver(string $fieldName): \Closure
    {
        return function (mixed $root, array $args, GraphQLContext $context, BaseResolveInfo $resolveInfo) use ($fieldName) {
            $subscription = resolve(BaseItemSubscription::class);
            $resolveInfo = new ResolveInfo($resolveInfo, new ArgumentSet);
            if ($root instanceof Subscriber) {
                return $subscription->resolve($root->root, $args, $context, $resolveInfo);
            }

            $subscriber = new Subscriber($args, $context, $resolveInfo);

            if (! $subscription->can($subscriber)) {
                throw new UnauthorizedSubscriber('Unauthorized subscription request');
            }

            resolve(SubscriptionRegistry::class)->subscriber(
                $subscriber,
                $subscription->encodeTopic($subscriber, $fieldName),
            );

            return null;
        };
    }

    public function getRecordFields(): array
    {
        return [
            'withMarkers' => $this->boolean(nullable: true),
            'withRelationships' => $this->boolean(nullable: true),
            'withAssignees' => $this->boolean(nullable: true),
            //            'withFeaturesTodos' => $this->boolean(nullable: true),
            //            'withFeaturesEvents' => $this->boolean(nullable: true),
            //            'withFeaturesDocuments' => $this->boolean(nullable: true),
            //            'withFeaturesLinks' => $this->boolean(nullable: true),
            //            'withFeaturesPins' => $this->boolean(nullable: true),
            //            'withFeaturesNotes' => $this->boolean(nullable: true),
            //            'withFeaturesTimekeeper' => $this->boolean(nullable: true),
        ];
    }
}
