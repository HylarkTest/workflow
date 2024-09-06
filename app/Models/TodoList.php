<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use Planner\Models\TodoList as BaseTodoList;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TodoList
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Todo> todos()
 *
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Todo, \App\Models\TodoList>
 */
class TodoList extends BaseTodoList implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Todo, \App\Models\TodoList> */
    use HasFeatureListMethods;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Todo>
     */
    public function children(): HasMany
    {
        return $this->todos();
    }
}
