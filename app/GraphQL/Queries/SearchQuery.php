<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Finder\Finder;
use App\GraphQL\AppContext;
use LighthouseHelpers\Pagination\Cursor;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use LighthouseHelpers\Pagination\PaginationResult;

class SearchQuery
{
    public function __construct(protected GlobalId $globalIdService, protected TypeRegistry $typeRegistry) {}

    /**
     * @param  null  $rootValue
     * @param array{
     *     query: string,
     *     first: int,
     *     after?: string,
     *     orderBy?: array<array{
     *         direction: string,
     *         field: string,
     *     }>,
     *     types?: string[],
     * } $args
     */
    public function __invoke($rootValue, array $args, AppContext $context): PaginationResult
    {
        if (! $args['query']) {
            return new PaginationResult(collect([]), [
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => false,
                'nextCursor' => null,
            ], 0);
        }
        $query = Finder::search($args['query']);

        $query->whereIn('__typename', $args['types'] ?? [
            'Todo',
            'TodoList',
            'Event',
            'Calendar',
            'Page',
            'Item',
            'Pin',
            'Pinboard',
            'Note',
            'Notebook',
            'Link',
            'LinkList',
            'Document',
            'Drive',
            'Marker',
        ]);

        $orders = $args['orderBy'] ?? [['field' => 'MATCH', 'direction' => 'DESC']];
        foreach ($orders as $order) {
            $direction = mb_strtolower($order['direction']);
            match ($order['field']) {
                'MATCH' => $query->orderBy('_score', $direction),
                'UPDATED_AT' => $query->orderBy('created_at', $direction),
                'CREATED_AT' => $query->orderBy('updated_at', $direction),
                default => null,
            };
        }

        $query->orderBy('__typename');
        $query->orderBy('id');

        return $query->cursorPaginate($args['first'], Cursor::decode(['after' => $args['after'] ?? null]));
    }
}
