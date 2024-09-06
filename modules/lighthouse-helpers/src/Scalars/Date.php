<?php

declare(strict_types=1);

namespace LighthouseHelpers\Scalars;

use GraphQL\Utils\Utils;
use Illuminate\Support\Carbon;
use GraphQL\Error\InvariantViolation;
use Nuwave\Lighthouse\Schema\Types\Scalars\Date as BaseDate;

class Date extends BaseDate
{
    /**
     * Serialize an internal value, ensuring it is a valid date string.
     *
     * @param  \Carbon\Carbon|string  $value
     *
     * @throws \GraphQL\Error\Error
     */
    public function serialize($value): string
    {
        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        $this->tryParsingDate((string) $value, InvariantViolation::class);

        return (string) $value;
    }

    /**
     * Try to parse the given value into a Carbon instance, throw if it does not work.
     *
     * @param  string  $value
     * @param  class-string<\Exception>  $exceptionClass
     *
     * @throws \GraphQL\Error\InvariantViolation
     */
    protected function tryParsingDate($value, string $exceptionClass): Carbon
    {
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Exception $e) {
            throw new $exceptionClass(Utils::printSafeJson($e->getMessage()));
        }
    }
}
