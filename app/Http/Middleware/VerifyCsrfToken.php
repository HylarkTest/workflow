<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'stripe/*',
    ];

    protected function isReading($request)
    {
        return parent::isReading($request)
            || $this->isNotGraphQLMutation($request);
    }

    protected function isNotGraphQLMutation(Request $request): bool
    {
        if (! $request->is('graphql')) {
            return true;
        }
        if ($request->has('operations')) {
            return true;
        }

        return ! preg_match('/^mutation/', $request->input('query'));
    }
}
