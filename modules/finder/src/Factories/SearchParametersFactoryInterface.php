<?php

declare(strict_types=1);

namespace Finder\Factories;

use Finder\Builder;
use Elastic\Adapter\Search\SearchParameters;

interface SearchParametersFactoryInterface
{
    public function makeFromBuilder(Builder $builder, array $options = []): SearchParameters;
}
