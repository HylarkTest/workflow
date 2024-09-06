<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends FeatureListRepository<\App\Models\Notebook>
 */
class NotebookRepository extends FeatureListRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Notebook>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->notebooks()->getQuery();
    }
}
