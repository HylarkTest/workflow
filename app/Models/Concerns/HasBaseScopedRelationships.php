<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use CitusLaravel\Database\Eloquent\DistributedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @property \App\Models\Base $base
 */
trait HasBaseScopedRelationships
{
    /**
     * @use \CitusLaravel\Database\Eloquent\DistributedModel<\App\Models\Base>
     */
    use DistributedModel;

    protected string $distributedModel = Base::class;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Base, self>
     *
     * @phpstan-ignore-next-line
     */
    public function base(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->distributedModel();
    }

    /**
     * @return EloquentBuilder<self>
     *
     * @phpstan-ignore-next-line
     */
    public function newModelQuery()
    {
        /** @var EloquentBuilder<self> $query */
        $query = parent::newModelQuery();

        if (! should_be_scoped($this)) {
            return $query;
        }
        $query->withGlobalScope('base', function (Builder $builder) {
            $base = tenancy()->tenant;
            if ($base) {
                $builder->where($this->qualifyColumn('base_id'), $base->getKey());
            } elseif (! app()->runningInConsole()) {
                throw new \RuntimeException('No tenant found when querying model '.__CLASS__);
            }
        });

        return $query;
    }

    public function getCurrentDistributedModel(): ?Model
    {
        /** @var \App\Models\Base $base */
        $base = tenancy()->tenant;

        return $base;
    }
}
