<?php

declare(strict_types=1);

namespace App\GraphQL\ErrorHandlers;

use GraphQL\Error\Error;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;
use AccountIntegrations\Exceptions\ResourceNotFoundException;
use LighthouseHelpers\Exceptions\MissingErrorHandler as BaseMissingErrorHandler;

class MissingErrorHandler extends BaseMissingErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        if ($error->getPrevious() instanceof ResourceNotFoundException) {
            return $next(new Error(
                'No results for the requested node(s).',
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new class extends \Exception implements ClientAware, ProvidesExtensions
                {
                    public function isClientSafe(): bool
                    {
                        return true;
                    }

                    public function getExtensions(): ?array
                    {
                        return ['category' => 'missing'];
                    }
                }
            ));
        }

        return parent::__invoke($error, $next);
    }
}
