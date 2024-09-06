<?php

declare(strict_types=1);

namespace App\GraphQL\ErrorHandlers;

use GraphQL\Error\Error;
use GraphQL\Error\ClientAware;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use AccountIntegrations\Exceptions\InvalidGrantException;

class InvalidGrantErrorHandler implements ErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof InvalidGrantException) {
            return $next(new Error(
                'Integrated account credentials are invalid.',
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new class($underlyingException->account) extends InvalidGrantException implements ClientAware
                {
                    public function isClientSafe(): bool
                    {
                        return true;
                    }
                },
                [
                    'redirect' => $underlyingException->account->renewRedirectUrl(),
                    'category' => 'redirect',
                ],
            ));
        }

        return $next($error);
    }
}
