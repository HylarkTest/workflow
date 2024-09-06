<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Items;

use App\Models\Base;
use GraphQL\Deferred;
use Mappings\Models\Item;
use App\GraphQL\AppContext;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Factory;
use GraphQL\Type\Definition\NonNull;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\QueryException;
use GraphQL\Type\Definition\ResolveInfo;
use LighthouseHelpers\Pagination\Cursor;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Type\Definition\InterfaceType;
use LighthouseHelpers\Core\LoaderDecorator;
use LighthouseHelpers\Pagination\PaginationArgs;
use Nuwave\Lighthouse\Pagination\PaginationType;
use LighthouseHelpers\Pagination\PaginationResult;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use LighthouseHelpers\Pagination\PaginatedModelsLoader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\GraphQL\Queries\Concerns\BroadcastsRelationshipsChanges;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;
use Nuwave\Lighthouse\Execution\ResolveInfo as LighthouseResolveInfo;

class ItemRelationshipQuery extends Mutation
{
    use BroadcastsRelationshipsChanges;

    public function __construct(protected GlobalId $globalId, Factory $validationFactory)
    {
        parent::__construct($validationFactory);
    }

    public function connectionResolver(Item $parent, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        return $this->resolveRelationship($parent, $args, $resolveInfo);
    }

    public function singleEdgeResolver(Item $parent, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        return $this->resolveRelationship($parent, $args, $resolveInfo);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Item>
     */
    public function edgeResolver(PaginationResult $paginator, array $args, AppContext $context, ResolveInfo $resolveInfo): Collection
    {
        // We know those types because we manipulated them during PaginationManipulator
        $nonNullList = $resolveInfo->returnType;
        \assert($nonNullList instanceof NonNull);

        $objectLikeType = $nonNullList->getInnermostType();
        \assert($objectLikeType instanceof ObjectType || $objectLikeType instanceof InterfaceType);

        $returnTypeFields = $objectLikeType->getFields();

        return $paginator->records->values()
            ->map(function ($item) use ($returnTypeFields): ?array {
                return $this->resolveItem($item, $returnTypeFields);
            });
    }

    /**
     * @param  null  $root
     */
    public function store($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \Mappings\Models\Item $item */
        $item = $base->items()->findOrFail($this->decodeId($args['input']['itemId'], 'Item'));
        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $item->mapping->relationships->find($args['input']['relationshipId']);
        $related = $this->fetchItems($base, $args['input']['ids']);

        DB::beginTransaction();

        try {
            /** @phpstan-ignore-next-line Should accept covariant type */
            $relationship->add($item, $related);
        } catch (QueryException $e) {
            DB::rollBack();
            $this->throwValidationException('input.ids', [trans('validation.custom.relationships.ids.unique')]);
        }

        DB::commit();

        return $this->mutationResponse(200, 'Relationship was added successfully', [
            $item->mapping->api_singular_name => $item,
        ]);
    }

    /**
     * @param  null  $root
     */
    public function destroy($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($this->decodeId($args['input']['itemId'], 'Item'));
        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $item->mapping->relationships->find($args['input']['relationshipId']);

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item>|null $related */
        $related = isset($args['input']['ids']) ? $this->fetchItems($base, $args['input']['ids']) : null;

        /** @phpstan-ignore-next-line Should accept covariant type */
        $relationship->remove($item, $related);

        $this->broadcastRelationshipChanges($item, $related);

        return $this->mutationResponse(200, 'Relationship was removed successfully', [
            $item->mapping->api_singular_name => $item,
        ]);
    }

    /**
     * @param  null  $root
     */
    public function update($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($this->decodeId($args['input']['itemId'], 'Item'));
        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $item->mapping->relationships->find($args['input']['relationshipId']);
        /** @var \App\Models\Item $related */
        $related = $this->fetchItems($base, [$args['input']['id']])->first();

        $relationship->set($item, $related);

        $this->broadcastRelationshipChanges($item, $related);

        return $this->mutationResponse(200, 'Relationship was updated successfully', [
            $item->mapping->api_singular_name => $item,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item>
     */
    protected function fetchItems(Base $base, array $ids): \Illuminate\Database\Eloquent\Collection
    {
        $types = array_map([$this->globalId, 'decodeType'], $ids);
        $ids = array_map([$this->globalId, 'decodeID'], $ids);
        if (array_unique($types) !== ['Item']) {
            throw (new ModelNotFoundException)->setModel(Item::class, $ids);
        }

        return $base->items()->findOrFail($ids);
    }

    protected function resolveItem(?Item $item, array $returnTypeFields): ?array
    {
        if (! $item) {
            return null;
        }

        $data = [];

        foreach ($returnTypeFields as $fieldType) {
            $fieldName = $fieldType->name;
            switch ($fieldName) {
                case 'cursor':
                    $data['cursor'] = Cursor::encode($item->getAttribute('cursor'));
                    break;

                case 'node':
                    $data['node'] = $item;
                    break;

                default:
                    // All other fields on the return type are assumed to be part
                    // of the edge, so we try to locate them in the pivot attribute
                    if (isset($item->getAttribute('pivot')->{$fieldName})) {
                        $data[$fieldName] = $item->getAttribute('pivot')->{$fieldName};
                    }
            }
        }

        return $data;
    }

    protected function resolveRelationship(Item $parent, array $args, ResolveInfo $resolveInfo): Deferred
    {
        $relationName = $resolveInfo->fieldName;
        $returnType = $resolveInfo->returnType;
        if ($returnType instanceof WrappingType) {
            $returnType = $returnType->getWrappedType();
        }
        \assert($returnType instanceof NamedType, 'Return type must be a named type');
        /** @phpstan-ignore-next-line We know it has a name */
        $toMany = Str::endsWith($returnType->name, 'Connection');

        /** @var \Nuwave\Lighthouse\Pagination\PaginationArgs|null $paginationArgs */
        $paginationArgs = null;
        $paginationType = $this->paginationType($toMany);
        $lighthouseResolveInfo = new LighthouseResolveInfo($resolveInfo, new ArgumentSet);
        if ($paginationType) {
            $paginationArgs = PaginationArgs::extractArgs($args, $lighthouseResolveInfo, $paginationType, $this->paginateMaxCount());
        }

        $decorateBuilder = fn () => null;

        /** @phpstan-ignore-next-line */
        $returnTypeFields = $returnType->getFields();

        $path = $resolveInfo->path;
        $path[] = $relationName;

        $relationName = "relatedItems__$relationName";

        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $relationBatchLoader */
        $relationBatchLoader = BatchLoaderRegistry::instance(
            $path,
            function () use ($relationName, $decorateBuilder, $paginationArgs, $toMany, $returnTypeFields): RelationBatchLoader {
                if ($paginationArgs === null) {
                    $modelsLoader = new SimpleModelsLoader($relationName, $decorateBuilder);
                } else {
                    $modelsLoader = new PaginatedModelsLoader($relationName, $decorateBuilder, $paginationArgs);
                }

                if (! $toMany) {
                    $modelsLoader = new LoaderDecorator($modelsLoader, fn (?Item $item) => $this->resolveItem($item, $returnTypeFields));
                }

                return new RelationBatchLoader($modelsLoader);
            }
        );

        return $relationBatchLoader->load($parent);
    }

    protected function paginationType(bool $toMany): ?PaginationType
    {
        if ($toMany) {
            return new PaginationType(PaginationType::CONNECTION);
        }

        return null;
    }

    protected function paginateMaxCount(): ?int
    {
        return config('lighthouse.paginate_max_count');
    }
}
