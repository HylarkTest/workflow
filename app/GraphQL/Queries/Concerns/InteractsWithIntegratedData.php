<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Scope;
use AccountIntegrations\Models\IntegrationAccount;
use LighthouseHelpers\Exceptions\ValidationException;

/**
 * @template TListItem
 */
trait InteractsWithIntegratedData
{
    /**
     * @param array{
     *     input?: array{sourceId: int|string|\AccountIntegrations\Models\IntegrationAccount},
     *     sourceId?: int|string|\AccountIntegrations\Models\IntegrationAccount,
     * } $args
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function getSource(AppContext $context, array $args, Scope $scope): IntegrationAccount
    {
        $user = $context->user();

        $path = isset($args['input']) ? 'input.sourceId' : 'sourceId';

        if (! Arr::has($args, $path)) {
            throw ValidationException::withMessages([$path => ['The sourceId field is required.']]);
        }

        $sourceId = Arr::get($args, $path);

        if ($sourceId instanceof IntegrationAccount) {
            $source = $sourceId;
            if ($source->account_owner_id !== $user->id || $source->account_owner_type !== $user->getMorphClass()) {
                throw new \Exception('Integration account does not belong to the user');
            }
        } else {
            /** @var \AccountIntegrations\Models\IntegrationAccount $source */
            $source = $user->integrationAccounts()->findOrFail($sourceId);
        }

        if (! $source->hasScope($scope)) {
            throw new \Exception('Integration is not supported for this method');
        }

        return $source;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, TListItem>|\Closure(): \Illuminate\Support\Collection<int, TListItem>  $data
     * @param  int|\Closure(): int  $count
     * @param  bool|\Closure(): bool  $hasMorePages
     */
    protected function buildExternalPaginator(
        Collection|\Closure $data,
        int|\Closure $count,
        bool|\Closure $hasMorePages,
        int $currentPage,
        int $perPage,
        ?int $total = null
    ): array {
        $firstItem = function () use ($count, $currentPage, $perPage) {
            /** @var int $count */
            $count = value($count);
            if ($count > 1) {
                return ($currentPage - 1) * $perPage + 1;
            }

            return null;
        };

        $lastItem = function () use ($count, $firstItem) {
            /** @var int $count */
            $count = value($count);
            if ($count > 0) {
                /** @var int $firstItem */
                $firstItem = $firstItem();

                return $firstItem + $count - 1;
            }

            return null;
        };

        return [
            'data' => $data,
            'paginatorInfo' => [
                'count' => $count,
                'currentPage' => $currentPage,
                'hasMorePages' => $hasMorePages,
                'firstItem' => $firstItem,
                'lastItem' => $lastItem,
                'perPage' => $perPage,
                'total' => $total,
            ],
        ];
    }
}
