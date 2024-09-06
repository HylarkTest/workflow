<?php

declare(strict_types=1);

namespace LighthouseHelpers\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasCamelCaseAttributes
 *
 * @mixin Model
 */
trait ConvertsCamelCaseAttributes
{
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        $snake = Str::snake($key);

        if (\array_key_exists($snake, $this->attributes)) {
            $key = $snake;
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(Str::snake($key), $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @phpstan-return \Illuminate\Database\Eloquent\Model
     */
    public function fill(array $attributes): Model
    {
        $snakeAttributes = [];

        foreach ($attributes as $key => $value) {
            $snakeAttributes[Str::snake($key)] = $value;
        }

        return parent::fill($snakeAttributes);
    }
}
