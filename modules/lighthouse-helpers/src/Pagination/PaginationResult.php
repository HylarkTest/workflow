<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use Lampager\Laravel\PaginationResult as BasePaginationResult;

class PaginationResult extends BasePaginationResult
{
    public int $total;

    public ?int $rawTotal;

    /**
     * PaginationResult constructor.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  array|\Illuminate\Support\Collection<int, TModel>  $rows
     */
    public function __construct($rows, array $meta, int $total, ?int $rawTotal = null)
    {
        $this->total = $total;
        $this->rawTotal = $rawTotal ?? $total;
        parent::__construct($rows, $meta);
    }
}
