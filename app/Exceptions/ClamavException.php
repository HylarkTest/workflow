<?php

declare(strict_types=1);

namespace App\Exceptions;

use Xenolope\Quahog\Result;

class ClamavException extends \Exception
{
    public static function forNonReadableFile(string $file): self
    {
        return new self("The file \"$file\" is not readable");
    }

    public static function forScanResult(Result $result): self
    {
        return new self(
            sprintf(
                'ClamAV scanner failed to scan file "%s" with error "%s"',
                $result->getFilename(),
                $result->getReason(),
            )
        );
    }

    public static function forClientException(\Exception $exception): self
    {
        return new self(
            sprintf('ClamAV scanner client failed with error "%s"', $exception->getMessage()),
            0,
            $exception
        );
    }

    public static function forMalware(Result $result): self
    {
        return new self(
            sprintf(
                'ClamAV scanner found a possible virus in "%s" with error "%s"',
                $result->getFilename(),
                $result->getReason(),
            )
        );
    }
}
