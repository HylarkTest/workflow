<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Exceptions;

class DriverDoesNotExistException extends LocationException
{
    public static function forDriver(string $driver): self
    {
        return new self(
            "The location driver [$driver] does not exist. Did you publish the configuration file?"
        );
    }
}
