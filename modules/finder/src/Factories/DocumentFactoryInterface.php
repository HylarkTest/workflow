<?php

declare(strict_types=1);

namespace Finder\Factories;

use Illuminate\Support\Collection;

interface DocumentFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Finder\GloballySearchable&\Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Support\Collection<int, \Elastic\Adapter\Documents\Document>
     */
    public function makeFromModels(Collection $models): Collection;
}
