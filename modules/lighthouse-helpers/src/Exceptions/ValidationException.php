<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use Illuminate\Validation\ValidationException as LaravelValidationException;
use Nuwave\Lighthouse\Exceptions\ValidationException as LighthouseValidationException;

class ValidationException extends LighthouseValidationException
{
    public function getExtensions(): array
    {
        return [
            ...parent::getExtensions(),
            'category' => 'validation',
        ];
    }

    public static function fromLaravel(LaravelValidationException|LighthouseValidationException $laravelException): LighthouseValidationException
    {
        /** @phpstan-ignore-next-line accessing protected method which works for now. Might need to override validate directive if this becomes a problem */
        return new self($laravelException->getMessage(), $laravelException->validator);
    }
}
