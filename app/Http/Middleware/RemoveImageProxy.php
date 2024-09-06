<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class RemoveImageProxy extends TransformsRequest
{
    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        $corsProxyUrl = config('hylark.cors_proxy_url');
        if (
            ! \is_string($value)
            || ! $corsProxyUrl
            || ! Str::startsWith($value, $corsProxyUrl)
        ) {
            return $value;
        }

        return str_replace($corsProxyUrl, '', $value);
    }
}
