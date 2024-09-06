<?php

declare(strict_types=1);

namespace AccountIntegrations\Exceptions;

use AccountIntegrations\Models\IntegrationAccount;

class InvalidGrantException extends \Exception
{
    public function __construct(public readonly IntegrationAccount $account)
    {
        parent::__construct();
    }
}
