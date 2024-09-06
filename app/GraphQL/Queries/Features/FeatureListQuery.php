<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Core\Mutation;
use App\Models\Contracts\FeatureList;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Features\Repositories\FeatureListRepository;

/**
 * @template TList of \App\Models\Contracts\FeatureList&\Illuminate\Database\Eloquent\Model
 */
abstract class FeatureListQuery extends Mutation
{
    public static function buildSchemaForFeatureList(string $query, string $childQuery, bool $canBeFavorited): string
    {
        $plural = Str::plural($query);
        $singular = Str::singular($query);
        $type = Str::studly($singular);

        $childPlural = Str::plural($childQuery);
        $upperChildPlural = ucfirst($childPlural);
        $childSingular = Str::singular($childQuery);
        $childType = Str::studly($childSingular);

        $favoriteField = $canBeFavorited ? 'isFavorite: Boolean! @method' : '';
        $favoriteInputField = $canBeFavorited ? 'isFavorite: Boolean' : '';
        $favoriteStatField = $canBeFavorited ? 'favoritesCount: Int!' : '';
        $favoriteFilterInput = $canBeFavorited ? 'isFavorited: Boolean' : '';

        $queryClass = "App\\GraphQL\\Queries\\Features\\{$type}Query";
        $childQueryClass = "App\\GraphQL\\Queries\\Features\\{$childType}Query";

        $childItemQueryParams = $childQueryClass::$itemQueryParams ?? '';

        $queryClass = addslashes($queryClass);
        $childQueryClass = addslashes($childQueryClass);

        $idField = $childType === 'Event' ? 'id: ID!, full: Boolean = true' : "id: ID! @globalId(decode: \"ID\", type: \"$childType\")";

        $childFilters = /** @lang GraphQL */ <<<GRAPHQL
        {$singular}Id: ID @globalId(decode: "ID", type: "$type")
        spaceId: ID @globalId(decode: "ID", type: "Space")
        forMapping: ID @globalId(decode: "ID", type: "Mapping")
        forNode: ID
        forLists: [ID!] @globalId(decode: "ID", type: "$type")
        filters: [{$childType}FilterInput!]
        $childItemQueryParams
        $favoriteFilterInput
        search: [String!]
        orderBy: [{$childType}OrderBy!]
        first: Int! = 25
        after: String
GRAPHQL;

        return /** @lang GraphQL */ <<<GRAPHQL
extend type Query @guard(with: ["web", "api"]) {
    $plural(
        spaceIds: [ID!] @globalId(decode: "ID", type: "Space")
        forLists: [ID!] @globalId(decode: "ID", type: "$type")
        refs: [String!]
        first: Int! = 25
        after: String
    ): {$type}Connection! @field(resolver: "$queryClass@index") @actionFilters
    $singular(id: ID! @globalId(decode: "ID", type: "$type")): $type @field(resolver: "$queryClass@show")

    {$childSingular}Stats(
        forMapping: ID @globalId(decode: "ID", type: "Mapping")
        forLists: [ID!] @globalId(decode: "ID", type: "$type")
        forNode: ID
    ): {$childType}Stats @field(resolver: "$childQueryClass@stats")

    $childPlural(
        $childFilters
    ): {$childType}Connection! @field(resolver: "$childQueryClass@index")

    grouped$upperChildPlural(
        group: String!
        includeGroups: [String!]
        $childFilters
    ): Grouped$upperChildPlural! @field(resolver: "$childQueryClass@index")

    $childSingular($idField): $childType @field(resolver: "$childQueryClass@show")
}

extend type Mutation @guard(with: ["web", "api"]) {
    create$type(input: Create{$type}Input!): {$type}MutationResponse! @field(resolver: "$queryClass@store") @broadcastNodeCreated(subscription: "{$singular}Created", nodeField: "{$singular}")
    update$type(input: Update{$type}Input!): {$type}MutationResponse! @field(resolver: "$queryClass@update") @broadcastNodeUpdated(subscription: "{$singular}Updated", nodeField: "{$singular}")
    delete$type(input: Delete{$type}Input!): SuccessfulMutationResponse! @field(resolver: "$queryClass@destroy") @broadcastNodeDeleted(subscription: "{$singular}Deleted")
    restore$type(input: Restore{$type}Input!): {$type}MutationResponse! @field(resolver: "$queryClass@restore") @broadcastNodeRestored(subscription: "{$singular}Restored", nodeField: "{$singular}")
    move$type(input: Move{$type}Input!): {$type}MutationResponse! @field(resolver: "$queryClass@move") @broadcastNodeUpdated(subscription: "{$singular}Moved", nodeField: "{$singular}")


    create$childType(input: Create{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@store") @broadcastNodeCreated(subscription: "{$childSingular}Created", nodeField: "{$childSingular}")
    update$childType(input: Update{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@update") @broadcastNodeUpdated(subscription: "{$childSingular}Updated", nodeField: "{$childSingular}")
    duplicate$childType(input: Duplicate{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@duplicate") @broadcastNodeCreated(subscription: "{$childSingular}Duplicated", nodeField: "{$childSingular}")
    delete$childType(input: Delete{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@destroy") @broadcastNodeDeleted(subscription: "{$childSingular}Deleted")
    restore$childType(input: Restore{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@restore") @broadcastNodeRestored(subscription: "{$childSingular}Restored", nodeField: "{$childSingular}")
    move$childType(input: Move{$childType}Input!): {$childType}MutationResponse! @field(resolver: "$childQueryClass@move") @broadcastNodeRestored(subscription: "{$childSingular}Moved", nodeField: "{$childSingular}")
}

extend type Subscription {
    {$singular}Created: {$type}MutationResponse
    {$singular}Updated: {$type}MutationResponse
    {$singular}Deleted: {$type}MutationResponse
    {$singular}Restored: {$type}MutationResponse
    {$singular}Moved: {$type}MutationResponse

    {$childSingular}Created: {$childType}MutationResponse
    {$childSingular}Updated: {$childType}MutationResponse
    {$childSingular}Duplicated: {$childType}MutationResponse
    {$childSingular}Deleted: {$childType}MutationResponse
    {$childSingular}Restored: {$childType}MutationResponse
    {$childSingular}Moved: {$childType}MutationResponse
}

type $type implements Findable & FeatureList & ActionSubject @node {
    spaceId: ID! @globalId(decode: "ID", type: "Space")
    name: String!
    {$childPlural}Count(forNode: ID, forMapping: ID @globalId(type: "Mapping", decode: "ID")): Int! @field(resolver: "$queryClass@resolveItemCount")
    $childPlural: [$childType!]! @hasMany(type: CONNECTION)
    order: Int!
    isDefault: Boolean!
    templateRefs: [String!]
    color: Color @color @method(name: "colorOrDefault")
    createdAt: DateTime!
    updatedAt: DateTime!
    space: Space! @belongsTo
}

type $childType implements FeatureItem & Findable & Markable & Associatable & Assignable & ActionSubject & FetchesActions {
    {$favoriteField}
    createdAt: DateTime!
    updatedAt: DateTime!
    $singular: $type! @belongsTo
    markerGroups: [MarkerCollection!] @field(resolver: "App\\\GraphQL\\\Queries\\\MarkerQuery@resolveCollection")
    associations: [Item!] @field(resolver: "App\\\GraphQL\\\Queries\\\Items\\\ItemQuery@resolveAssociations")
    assigneeGroups: [AssigneeInfo!]! @field(resolver: "App\\\GraphQL\\\Queries\\\AssigningQuery@resolveAssignees")
    createAction: Action @field(resolver: "App\\\GraphQL\\\Queries\\\HistoryQuery@resolveCreateAction")
    latestAction: Action @field(resolver: "App\\\GraphQL\\\Queries\\\HistoryQuery@resolveLatestAction")
}

input {$childType}OrderBy {
    direction: SortOrder!
    field: {$childType}OrderField!
}

input {$childType}FilterInput {
    boolean: BooleanOperator = AND
    filters: [{$childType}FilterInput!]
    markers: [MarkerFilterInput!]
    search: [String!]
    $favoriteFilterInput
}

type {$childType}Stats {
    $favoriteStatField
    totalCount: Int!
}

type Grouped$upperChildPlural {
    groups: [Grouped{$childType}Connection!]!
}

type Grouped{$childType}Connection implements FeatureItemConnection {
    groupHeader: String
    group: Groupable
    edges: [{$childType}Edge!]! @connectionEdge
    pageInfo: PageInfo! @pageInfo
}


type {$type}Edge {
    node: $type!
    cursor: String!
}

type {$type}Connection {
    edges: [{$type}Edge!]! @connectionEdge
    pageInfo: PageInfo! @pageInfo
}

type {$childType}Edge implements FeatureItemEdge {
    node: $childType!
    cursor: String!
}

type {$childType}Connection implements FeatureItemConnection {
    edges: [{$childType}Edge!]! @connectionEdge
    pageInfo: PageInfo! @pageInfo
}

type {$type}MutationResponse implements MutationResponse {
    code: String!
    success: Boolean!
    message: String!
    $singular: $type
}

type {$childType}MutationResponse implements MutationResponse {
    code: String!
    success: Boolean!
    message: String!
    $childSingular: $childType
    $singular: $type
}

input Create{$type}Input {
    spaceId: ID! @globalId(decode: "ID", type: "Space")
    name: String! @rules(apply: ["required", "max:255"])
    color: Color
    templateRefs: [String!] @rules(apply: ["filled", "max:255"]) @rulesForArray(apply: ["max:10"])
}

input Update{$type}Input {
    id: ID! @globalId(decode: "ID", type: "$type")
    name: String! @rules(apply: ["filled", "max:255"])
    color: Color
}

input Delete{$type}Input {
    id: ID! @globalId(decode: "ID", type: "$type")
    force: Boolean
}

input Restore{$type}Input {
    id: ID! @globalId(decode: "ID", type: "$type")
}

input Move{$type}Input {
    id: ID! @globalId(decode: "ID", type: "$type")
    previousId: ID @globalId(decode: "ID", type: "$type")
}

input Create{$childType}Input {
    {$singular}Id: ID! @globalId(decode: "ID", type: "$type")
    markers: [MarkersInput!]
    associations: [ID!] @globalId(type: "Item", decode: "ID")
    assigneeGroups: [AssigneesInput!]
}

input Update{$childType}Input {
    {$singular}Id: ID @globalId(decode: "ID", type: "$type")
    $idField
    $favoriteInputField
}

input Duplicate{$childType}Input {
    $idField
    withMarkers: Boolean = false
    withAssociations: Boolean = false
    withAssignees: Boolean = false
}

input Move{$childType}Input {
    {$singular}Id: ID @globalId(decode: "ID", type: "$type")
    $idField
    previousId: ID @globalId(decode: "ID", type: "$childType")
}

input Delete{$childType}Input {
    $idField
    force: Boolean = false
}

input Restore{$childType}Input {
    id: ID! @globalId(decode: "ID", type: "$childType")
}
GRAPHQL;
    }

    /**
     * @param  null  $rootValue
     * @param array{
     *     first: int,
     *     after?: string,
     *     spaceIds?: int[],
     *     forLists?: int[],
     *     refs?: string[],
     * } $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $this->createDefaultLists($base);

        return $this->repository()->paginateFeatureLists(
            $base,
            $args,
            ($args['spaceIds'] ?? null) ? $args['spaceIds'] : null,
            $args['forLists'] ?? null,
            $args['refs'] ?? null,
        );
    }

    /**
     * @param  TList  $rootValue
     */
    public function resolveItemCount(FeatureList $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        return $this->repository()->getDeferredItemCountForList(
            $context->base(),
            $rootValue,
            $resolveInfo->path,
            $args['forNode'] ?? null,
            isset($args['forMapping']) ? (int) $args['forMapping'] : null,
        );
    }

    /**
     * @param  null  $rootValue
     * @return TList
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): FeatureList
    {
        return $this->repository()->getFeatureList($context->base(), (int) $args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $this->validateData($args, $context, $resolveInfo);

        $data = $this->getCreateData($args['input']);

        $list = $this->repository()->createFeatureList($context->base(), $data);

        return $this->mutationResponse(200, $this->getSuccessMessage('created'), [
            $this->getListKey() => $list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $this->validateData($args, $context, $resolveInfo);

        $data = $this->getUpdateData($args['input']);

        $list = $this->repository()->updateFeatureList($context->base(), (int) $args['input']['id'], $data);

        return $this->mutationResponse(200, $this->getSuccessMessage('updated'), [
            $this->getListKey() => $list,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function restore($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $list = $this->repository()->restoreFeatureList($context->base(), (int) $args['input']['id']);

        if ($list) {
            return $this->mutationResponse(200, $this->getSuccessMessage('restored'), [
                $this->getListKey() => $list,
            ]);
        }

        return $this->mutationResponse(400, $this->getFailureMessage('restored'));
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $success = $this->repository()->deleteFeatureList(
            $base,
            (int) $args['input']['id'],
            $args['input']['force'] ?? false,
        );

        if ($success) {
            return $this->mutationResponse(201, $this->getSuccessMessage('deleted'));
        }

        return $this->mutationResponse(400, $this->getFailureMessage('deleted'));
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $list = $this->repository()->moveFeatureList(
            $context->base(),
            (int) $args['input']['id'],
            isset($args['input']['previousId']) ? ((int) $args['input']['previousId']) : null
        );

        return $this->mutationResponse(200, $this->getSuccessMessage('moved'), [
            $this->getListKey() => $list,
        ]);
    }

    protected function getSuccessMessage(string $action): string
    {
        return ucfirst(Str::slug($this->getListKey(), ' '))." was $action successfully.";
    }

    protected function getFailureMessage(string $action): string
    {
        return ucfirst(Str::slug($this->getListKey(), ' '))." could not be $action successfully.";
    }

    protected function getCreateData(array $input): array
    {
        return Arr::only($input, ['spaceId', 'name', 'color', 'templateRefs']);
    }

    protected function getUpdateData(array $input): array
    {
        return Arr::only($input, ['name', 'color']);
    }

    /**
     * @throws \Exception
     */
    protected function validateData(array $args, AppContext $context, ResolveInfo $resolveInfo): void
    {
        $this->validate(
            data: $args['input'],
            rules: [
                'name' => [
                    'required',
                    'string',
                ],
            ],
            resolveInfo: $resolveInfo,
            messages: [
                'name.unique' => trans('validation.custom.'.$this->getListKey().'.name.unique'),
            ],
        );
    }

    abstract protected function createDefaultLists(Base $base): void;

    /**
     * @return \App\Core\Features\Repositories\FeatureListRepository<TList>
     */
    abstract protected function repository(): FeatureListRepository;

    abstract protected function getListKey(): string;
}
