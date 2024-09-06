<?php

declare(strict_types=1);

namespace Hylark\ArticleContent\Http\Middleware;

use Closure;

class Middleware
{
    public function handle($request, Closure $next)
    {
        /**
         * Check if the request coming from the same origin
         */
        $accepted_origins = [config('app.url')];
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
                header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
            } else {
                header('HTTP/1.1 403 Origin Denied');

                return response()->json(['error' => 'Origin denied']);
            }
        }

        return $next($request);
    }
}
