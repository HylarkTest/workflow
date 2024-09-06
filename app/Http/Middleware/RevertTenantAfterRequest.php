<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenancy for Laravel expects the application to be rebuilt with each request
 * so there is no need to revert the tenant after each request. However with
 * Laravel Octane this isn't the case. What occasionally happens is the
 * bootstrappers can get reverted but the application thinks the tenant is still
 * initialised between requests, when it needs to be initialised for every
 * request.
 *
 * This middleware simply ends the tenancy session after the request so it can
 * start from fresh at the next request.
 */
class RevertTenantAfterRequest
{
    public function __construct(protected Tenancy $tenancy) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle($request, $next): Response
    {
        $response = $next($request);

        $this->tenancy->end();

        return $response;
    }
}
