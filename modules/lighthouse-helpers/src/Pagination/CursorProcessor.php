<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use Lampager\Query;
use Illuminate\Support\Str;
use Lampager\Laravel\Processor;

class CursorProcessor extends Processor
{
    public int $total = 0;

    public int $rawTotal = 0;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
     */
    public function process(Query $query, $rows)
    {
        $this->builder = $query->builder();
        foreach ($rows as $row) {
            $cursor = $this->makeCursor($query, $row);
            $row->setAttribute('cursor', $cursor);
        }

        return parent::process($query, $rows);
    }

    /**
     * Format result with default format.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  array|\Illuminate\Support\Collection<int, TModel>  $rows
     * @return \LighthouseHelpers\Pagination\PaginationResult
     */
    protected function defaultFormat($rows, array $meta, Query $query)
    {
        return new PaginationResult($rows, $meta, $this->total, $this->rawTotal);
    }

    protected function field($row, $column)
    {
        if (Str::contains($column, '.')) {
            $column = last(explode('.', $column));
        }

        return parent::field($row, $column);
    }
}
