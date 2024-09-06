<?php

declare(strict_types=1);

namespace Finder\Factories;

use Illuminate\Support\Collection;
use Elastic\Adapter\Documents\Routing;

interface RoutingFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Finder\GloballySearchable&\Illuminate\Database\Eloquent\Model>  $models
     */
    public function makeFromModels(Collection $models): Routing;
}
