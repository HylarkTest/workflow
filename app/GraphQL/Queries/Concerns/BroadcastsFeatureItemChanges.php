<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use Illuminate\Support\Str;
use App\Models\Contracts\FeatureListItem;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use LighthouseHelpers\Concerns\BuildsGraphQLResponses;

/**
 * @template TList of \App\Models\Contracts\FeatureList
 * @template TItem of \App\Models\Contracts\FeatureListItem
 */
trait BroadcastsFeatureItemChanges
{
    use BuildsGraphQLResponses;

    /**
     * @param  \App\Models\Contracts\FeatureListItem<TList, TItem>  $item
     */
    protected function broadcastFeatureItemChange(FeatureListItem $item): void
    {
        $typeName = $item->typeName();
        $fieldName = Str::lcfirst($typeName);
        $event = "{$fieldName}Updated";
        Subscription::broadcast($event, $this->mutationResponse(200, "$typeName updated successfully", [$fieldName => $item]));
        Subscription::broadcast('nodeUpdated', $this->mutationResponse(200, "$typeName updated successfully", [
            'node' => $item,
            'event' => $event,
        ]));
    }
}
