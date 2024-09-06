<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Illuminate\Support\Collection;
use App\Models\Contracts\NotScoped;
use Elastic\Adapter\Documents\Routing;
use Finder\Factories\RoutingFactoryInterface;

class FinderRoutingFactory implements RoutingFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function makeFromModels(Collection $models): Routing
    {
        $routing = new Routing;

        foreach ($models as $model) {
            if ($value = $this->getShardRoute($model)) {
                $routing->add((string) $model->getFinderKey(), (string) $value);
            }
        }

        return $routing;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable  $model
     *
     * @throws \Exception
     */
    protected function getShardRoute($model): ?string
    {
        $definedShardRoute = (string) $model->shardRouting();
        if ($definedShardRoute) {
            return $definedShardRoute;
        }
        if ($model instanceof NotScoped) {
            return null;
        }
        $shardRoute = (string) $model->getAttribute('base_id');
        if (! $shardRoute) {
            throw new \Exception('Cannot route model without a base ID');
        }

        return $shardRoute;
    }
}
