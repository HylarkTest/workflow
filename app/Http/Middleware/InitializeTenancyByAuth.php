<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Stancl\Tenancy\Tenancy;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByAuth
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
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if ($user) {
            if ($baseId = $request->header('X-Base-Id')) {
                [$type, $id] = app(GlobalId::class)->decode($baseId);
                abort_if($type !== 'Base', 404);
                $base = $user->bases()->findOrFail($id);
            } else {
                $base = $user->getActiveBase();
            }
            $this->tenancy->initialize($base);
        }

        return $next($request);
    }
}
