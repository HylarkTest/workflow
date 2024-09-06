<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Stancl\Tenancy\Tenancy;
use Illuminate\Contracts\Auth\Access\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeTenant
{
    public function __construct(protected Tenancy $tenancy, protected Gate $gate) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle($request, $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        $tenant = $this->tenancy->tenant;

        $this->gate->forUser($user)->authorize('access', [$tenant]);

        return $next($request);
    }
}
