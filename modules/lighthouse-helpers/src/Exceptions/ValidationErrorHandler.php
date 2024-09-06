<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use GraphQL\Error\Error;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Nuwave\Lighthouse\Exceptions\ValidationException as LighthouseValidationException;

class ValidationErrorHandler extends \Nuwave\Lighthouse\Execution\ValidationErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof LaravelValidationException || $underlyingException instanceof LighthouseValidationException) {
            return $next(new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                ValidationException::fromLaravel($underlyingException),
            ));
        }

        return $next($error);
    }
}
