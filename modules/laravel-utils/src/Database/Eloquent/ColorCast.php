<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent;

use Color\Color;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ColorCast implements CastsAttributes
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Color\Color|string|null  $value
     * @return \Color\Color|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Color) {
            return $value;
        }

        return $value ? Color::make($value) : null;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Color\Color|string|null  $value
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Color) {
            return (string) $value->toHex();
        }

        return $value;
    }
}
