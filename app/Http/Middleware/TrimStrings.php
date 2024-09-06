<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        'moneyFormat.separator',
    ];

    protected function transform($key, $value)
    {
        if (Str::endsWith($key, '.insert') || Str::endsWith($key, '.text')) {
            return $value;
        }

        return parent::transform($key, $value);
    }
}
