<?php

declare(strict_types=1);

namespace LighthouseHelpers\Scalars;

class TimeZone extends StringScalar
{
    protected function isValid(string $stringValue): bool
    {
        try {
            new \DateTimeZone($stringValue);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
