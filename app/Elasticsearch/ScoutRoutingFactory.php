<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Illuminate\Support\Collection;
use Elastic\Adapter\Documents\Routing;
use Illuminate\Database\Eloquent\Model;
use Elastic\ScoutDriverPlus\Factories\RoutingFactoryInterface;

class ScoutRoutingFactory implements RoutingFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $models
     */
    public function makeFromModels(Collection $models): Routing
    {
        $routing = new Routing;

        foreach ($models as $model) {
            if ($value = $this->getShardRoute($model)) {
                /** @phpstan-ignore-next-line Cannot tell it to expect traits */
                $routing->add((string) $model->getScoutKey(), (string) $value);
            }
        }

        return $routing;
    }

    protected function getShardRoute(Model $model): ?string
    {
        if (method_exists($model, 'shardRouting')) {
            $definedShardRoute = $model->shardRouting();
            if ($definedShardRoute) {
                return $definedShardRoute;
            }
        }
        if (! should_be_scoped($model)) {
            return null;
        }
        $shardRoute = $model->getAttribute('base_id');
        if (! $shardRoute) {
            throw new \Exception('Cannot route model without a base ID');
        }

        return (string) $shardRoute;
    }
}
