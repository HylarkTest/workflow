<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use GraphQL\Error\Error;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Pagination\PaginationArgs as BasePaginationArgs;

class PaginationArgs extends BasePaginationArgs
{
    public array $cursor;

    /**
     * Empty constructor to allow creating static instance
     */
    final public function __construct() {}

    /**
     * Create a new instance from user given args.
     *
     * @param  array<string, mixed>  $args
     *
     * @throws \GraphQL\Error\Error
     */
    public static function extractArgs(array $args, ResolveInfo $resolveInfo, PaginationType $proposedPaginationType, ?int $paginateMaxCount): BasePaginationArgs
    {
        if ($proposedPaginationType->isPaginator()) {
            return parent::extractArgs($args, $resolveInfo, $proposedPaginationType, $paginateMaxCount);
        }

        $instance = new static;

        $instance->type = $proposedPaginationType;

        $instance->first = $args['first'];
        $instance->cursor = Cursor::decode($args);

        if ($instance->first < 0) {
            throw new Error(self::requestedLessThanZeroItems($instance->first));
        }

        // Make sure the maximum pagination count is not exceeded
        if (
            $paginateMaxCount !== null
            && $instance->first > $paginateMaxCount
        ) {
            throw new Error(self::requestedTooManyItems($paginateMaxCount, $instance->first));
        }

        return $instance;
    }
}
