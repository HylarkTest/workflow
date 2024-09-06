<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Finder\CanBeGloballySearched;

trait ScoutAndFinderSearchable
{
    use CanBeGloballySearched, Searchable {
        CanBeGloballySearched::shardRouting insteadof Searchable;
        CanBeGloballySearched::usesSoftDelete insteadof Searchable;
    }
}
