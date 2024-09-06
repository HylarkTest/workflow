<?php

declare(strict_types=1);

namespace Finder\Events;

use Illuminate\Database\Eloquent\Collection;

class ModelsImported
{
    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function __construct(public Collection $models) {}
}
