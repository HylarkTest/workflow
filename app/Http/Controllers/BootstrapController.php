<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Nuwave\Lighthouse\GraphQL;
use Illuminate\Http\JsonResponse;
use App\Core\Bootstrap\Bootstrapper;
use App\Http\Requests\BootstrapRequest;
use Symfony\Component\HttpFoundation\Response;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext;
use Nuwave\Lighthouse\Support\Contracts\CreatesResponse;

class BootstrapController extends Controller
{
    public function __invoke(
        BootstrapRequest $request,
        Bootstrapper $bootstrapper,
        GraphQL $graphQL,
        CreatesContext $createsContext,
        CreatesResponse $createsResponse
    ): Response {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $bootstrapper->bootstrap($user, $request->baseData());

        $user->finished_registration_at = now();
        $user->save();
        /** @var \App\Models\Base $activeBase */
        /** @phpstan-ignore-next-line User exists */
        $activeBase = $user->fresh()->bases->last();
        $user->setActiveBase($activeBase);

        tenancy()->initialize($activeBase);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
