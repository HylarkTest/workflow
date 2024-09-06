<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Mapping;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use LighthouseHelpers\Concerns\BuildsGraphQLResponses;

trait BroadcastsMappingChanges
{
    use BuildsGraphQLResponses;

    protected function mappingMutationResponse(Mapping $mapping, string $message = 'Blueprint updated successfully'): array
    {
        return $this->mutationResponse(
            200,
            $message,
            ['mapping' => $mapping]
        );
    }

    protected function broadcastMappingUpdated(Mapping $mapping, string $message = 'Blueprint updated successfully'): void
    {
        Subscription::broadcast(
            'mappingUpdated',
            $this->mappingMutationResponse($mapping, $message)
        );
    }
}
