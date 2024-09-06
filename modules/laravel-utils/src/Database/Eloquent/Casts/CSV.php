<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<array, string>
 */
class CSV implements CastsAttributes
{
    /**
     * Create a new cast class instance.
     *
     * @param  non-empty-string  $delimiter
     * @return void
     */
    public function __construct(protected string $delimiter = ',') {}

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $attributes
     * @param  mixed  $value
     */
    public function get($model, $key, $value, $attributes): ?array
    {
        if ($value === null) {
            return null;
        }
        if ($value === '') {
            return [];
        }
        $result = explode($this->delimiter, $value);
        if ($result === false) {
            throw new \Exception('Could not explode CSV value');
        }

        return $result;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  string|array|null  $value
     * @param  array  $attributes
     */
    public function set($model, $key, $value, $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }

        return implode($this->delimiter, $value);
    }
}
