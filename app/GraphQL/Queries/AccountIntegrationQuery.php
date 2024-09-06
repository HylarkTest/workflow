<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\AppContext;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;

class AccountIntegrationQuery extends Mutation
{
    /**
     * @param  null  $rootValue
     * @return \Traversable<int, \AccountIntegrations\Models\IntegrationAccount>
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): \Traversable
    {
        $user = $context->user();

        return $user->integrationAccounts()
            ->orderBy('id')->getResults();
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();
        $id = $args['input']['id'];

        $user->integrationAccounts()->findOrFail($id)->delete();

        return $this->mutationResponse(201, 'Integration has been deleted successfully');
    }
}
