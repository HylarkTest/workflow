<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LaravelUtils\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsToMany<TRelatedModel>
 */
class BelongsToOne extends BelongsToMany
{
    use InteractsWithPivotTable;
}
