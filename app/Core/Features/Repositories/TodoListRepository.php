<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends FeatureListRepository<\App\Models\TodoList>
 */
class TodoListRepository extends FeatureListRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\TodoList>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->todoLists()->getQuery();
    }
}
