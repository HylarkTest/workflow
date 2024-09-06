<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Trait HasUniqueColumns
 *
 * A trait that automatically increments unique column values if that value
 * already exists in the database.
 * Instead of making a query to check it attempts to save the model normally
 * which will work 99% of the time but if there is a duplicate value, the
 * SQL exception will be caught and the offending value is incremented and
 * saved again.
 *
 * @mixin Model
 *
 * @property array $uniqueColumns
 */
trait HasUniqueColumns
{
    public function save(array $options = []): bool
    {
        if (! $this->uniqueColumns) {
            return parent::save($options);
        }
        try {
            return parent::save($options);
        } catch (QueryException $exception) {
            $message = $exception->getPrevious()?->getMessage() ?: '';
            $changed = false;
            foreach ($this->uniqueColumns as $column) {
                if (Str::contains($message, $column)) {
                    $this->attributes[$column] = $this->incrementColumnValue($this->attributes[$column]);
                    $changed = true;
                }
            }

            if ($changed) {
                return $this->save($options);
            }

            throw $exception;
        }
    }

    protected function incrementColumnValue(string $value): string
    {
        return increment_string_suffix($value);
    }
}
