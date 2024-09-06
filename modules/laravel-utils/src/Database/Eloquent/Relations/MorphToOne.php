<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelatedModel>
 */
class MorphToOne extends MorphToMany
{
    use InteractsWithPivotTable;

    protected function buildConstraints(): array
    {
        return [
            ! $this->inverse ? $this->foreignPivotKey : $this->relatedPivotKey => $this->parent->{$this->parentKey},
            $this->morphType => $this->morphClass,
        ];
    }
}
