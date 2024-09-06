<?php

declare(strict_types=1);

namespace App\Core\Imports;

class RowImportFailedException extends \Exception
{
    public function __construct(public int $row, public string $reason)
    {
        parent::__construct($reason);
    }
}
