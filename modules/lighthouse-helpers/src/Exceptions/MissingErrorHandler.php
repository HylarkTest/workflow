<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use GraphQL\Error\Error;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Illuminate\Database\Eloquent\ModelNotFoundException as LaravelModelNotFoundException;

class MissingErrorHandler implements ErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof LaravelModelNotFoundException) {
            $newException = ModelNotFoundException::fromLaravel($underlyingException);

            return $next(new Error(
                $newException->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $newException
            ));
        }
        if ($underlyingException instanceof GlobalIdException) {
            $newException = new ModelNotFoundException(
                $underlyingException->getMessage(),
                $underlyingException->getCode(),
                $underlyingException
            );

            return $next(new Error(
                $newException->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $newException
            ));
        }

        return $next($error);
    }
}
