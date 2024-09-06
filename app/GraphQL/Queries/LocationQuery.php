<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Lampager\Paginator;
use App\Models\Location;
use App\Core\LocationLevel;
use App\GraphQL\AppContext;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;

class LocationQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param  array{first: int, after?: string, levels?: string[], country?: string, search?: string, orderBy?: array}  $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $query = Location::query();

        if (isset($args['levels'])) {
            $levelCases = collect(LocationLevel::cases());
            $levels = array_map(fn (string $level) => $levelCases->firstWhere('name', $level)?->value, $args['levels']);
            $query->whereIn('level', $levels);
        }

        if (isset($args['country'])) {
            $query->where('country_geoname_id', $args['country']);
        }

        if (isset($args['search'])) {
            $query->where('name', 'like', "%{$args['search']}%");
        }

        return $this->paginateQuery($query, $args, function (Paginator $lampager) use ($args) {
            foreach ($args['orderBy'] ?? (isset($args['search']) ? [
                ['field' => 'population', 'direction' => 'DESC'],
            ] : [
                ['field' => 'level', 'direction' => 'ASC'],
                ['field' => 'name', 'direction' => 'ASC'],
            ]) as $orderBy) {
                if ($orderBy['field'] === 'match') {
                    $orderBy['field'] = 'population';
                }
                $lampager->orderBy($orderBy['field'], $orderBy['direction']);
            }
        });
    }
}
