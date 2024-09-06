<?php

declare(strict_types=1);

namespace Finder\Factories;

use Illuminate\Support\Collection;
use Elastic\Adapter\Documents\Routing;

class RoutingFactory implements RoutingFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Finder\GloballySearchable&\Illuminate\Database\Eloquent\Model>  $models
     */
    public function makeFromModels(Collection $models): Routing
    {
        $routing = new Routing;

        foreach ($models as $model) {
            if ($value = $model->shardRouting()) {
                $routing->add((string) $model->getFinderKey(), (string) $value);
            }
        }

        return $routing;
    }
}
