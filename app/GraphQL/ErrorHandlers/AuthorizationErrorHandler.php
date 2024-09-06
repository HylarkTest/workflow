<?php

declare(strict_types=1);

namespace App\GraphQL\ErrorHandlers;

use GraphQL\Error\Error;
use GraphQL\Error\ProvidesExtensions;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Illuminate\Auth\Access\AuthorizationException as LaravelAuthorizationException;
use Nuwave\Lighthouse\Execution\AuthorizationErrorHandler as BaseAuthorizationErrorHandler;

class AuthorizationErrorHandler extends BaseAuthorizationErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof LaravelAuthorizationException) {
            return $next(new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new class($underlyingException->getMessage(), $underlyingException->getCode()) extends AuthorizationException implements ProvidesExtensions
                {
                    public function getExtensions(): ?array
                    {
                        return ['category' => 'unauthorized'];
                    }
                }
            ));
        }

        return parent::__invoke($error, $next);
    }
}
