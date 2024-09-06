<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\InterfaceType;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ConnectionField
{
    /**
     * Resolve page info for connection.
     *
     * @param  \LighthouseHelpers\Pagination\PaginationResult  $paginator
     */
    public function pageInfoResolver($paginator): array
    {
        return [
            'total' => $paginator->total,
            'rawTotal' => $paginator->rawTotal ?? null,
            'count' => $paginator->count(),
            'hasNextPage' => $paginator->hasNext,
            'hasPreviousPage' => $paginator->hasPrevious,
            'startCursor' => $paginator->count() ? Cursor::encode($paginator->values()->first()->getAttribute('cursor')) : null,
            'endCursor' => $paginator->nextCursor ? Cursor::encode($paginator->nextCursor) : null,
        ];
    }

    /**
     * Resolve edges for connection.
     *
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $paginator
     * @param  array  $args
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function edgeResolver($paginator, $args, GraphQLContext $context, ResolveInfo $resolveInfo): \Illuminate\Support\Collection
    {
        // We know those types because we manipulated them during PaginationManipulator
        $nonNullList = $resolveInfo->returnType;
        \assert($nonNullList instanceof NonNull);

        $objectLikeType = $nonNullList->getInnermostType();
        \assert($objectLikeType instanceof ObjectType || $objectLikeType instanceof InterfaceType);

        $returnTypeFields = $objectLikeType->getFields();

        return $paginator
            ->values()
            ->map(function ($item) use ($returnTypeFields): array {
                $data = [];

                foreach ($returnTypeFields as $field) {
                    switch ($field->name) {
                        case 'cursor':
                            $data['cursor'] = Cursor::encode($item->getAttribute('cursor'));
                            break;

                        case 'node':
                            $data['node'] = $item;
                            break;

                        default:
                            // All other fields on the return type are assumed to be part
                            // of the edge, so we try to locate them in the pivot attribute
                            /** @var \Illuminate\Database\Eloquent\Model|null $pivot */
                            $pivot = $item->getAttribute('pivot');
                            if ($pivot) {
                                $data[$field->name] = $pivot->getAttribute($field->name);
                            }
                    }
                }

                return $data;
            });
    }
}
