<?php

declare(strict_types=1);

namespace App\GraphQL;

use Illuminate\Http\Request;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AppContextFactory implements CreatesContext
{
    public function generate(?Request $request): GraphQLContext
    {
        /** @var \App\Models\User $user */
        $user = ($request ?: request())->user();
        /** @var \App\Models\Base $base */
        $base = tenancy()->tenant;

        return new AppContext($user, $base);
    }
}
