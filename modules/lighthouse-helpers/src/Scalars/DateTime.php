<?php

declare(strict_types=1);

namespace LighthouseHelpers\Scalars;

use GraphQL\Utils\Utils;
use Illuminate\Support\Carbon;
use GraphQL\Error\InvariantViolation;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime as BaseDateTime;

class DateTime extends BaseDateTime
{
    /**
     * Serialize an internal value, ensuring it is a valid datetime string.
     *
     * @param  \Illuminate\Support\Carbon|string  $value
     *
     * @throws \GraphQL\Error\Error
     */
    public function serialize($value): string
    {
        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        $this->tryParsingDate($value, InvariantViolation::class);

        return $value;
    }

    /**
     * Try to parse the given value into a Carbon instance, throw if it does not work.
     *
     * @param  class-string<\Exception>  $exceptionClass
     * @param  mixed  $value
     *
     * @throws \GraphQL\Error\InvariantViolation|\GraphQL\Error\Error
     */
    protected function tryParsingDate($value, string $exceptionClass): Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            throw new $exceptionClass(Utils::printSafeJson($e->getMessage()));
        }
    }
}
