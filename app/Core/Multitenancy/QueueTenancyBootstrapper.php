<?php

declare(strict_types=1);

namespace App\Core\Multitenancy;

use App\Models\BaseUserPivot;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper as BaseQueueTenancyBootstrapper;

class QueueTenancyBootstrapper extends BaseQueueTenancyBootstrapper
{
    public function getPayload(string $connection): array
    {
        $payload = parent::getPayload($connection);

        $base = tenant();
        if ($payload && $base?->pivot) {
            $payload['pivot_id'] = $base->pivot->id;
        }

        return $payload;
    }

    /**
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @param  bool  $runningTests
     * @return void
     */
    protected static function setUpJobListener($dispatcher, $runningTests)
    {
        parent::setUpJobListener($dispatcher, $runningTests);

        $dispatcher->listen(JobProcessing::class, function ($event) {
            static::addPivotToTenantForQueue($event->job->payload()['pivot_id'] ?? null);
        });

        $dispatcher->listen(JobRetryRequested::class, function ($event) {
            static::addPivotToTenantForQueue($event->payload()['pivot_id'] ?? null);
        });

    }

    protected static function addPivotToTenantForQueue(?int $pivotId): void
    {
        if (! $pivotId) {
            return;
        }
        $pivot = BaseUserPivot::find($pivotId);

        if (! $pivot) {
            return;
        }

        $base = tenant();

        if ($base?->id !== $pivot->base_id) {
            return;
        }

        $base->setRelation('pivot', $pivot);
    }
}
