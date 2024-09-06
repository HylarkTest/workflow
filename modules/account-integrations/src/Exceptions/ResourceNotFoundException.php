<?php

declare(strict_types=1);

namespace AccountIntegrations\Exceptions;

use Illuminate\Database\RecordsNotFoundException;
use AccountIntegrations\Models\IntegrationAccount;

class ResourceNotFoundException extends RecordsNotFoundException
{
    /**
     * @var class-string
     */
    protected string $resourceClass;

    protected string $id;

    protected IntegrationAccount $integration;

    /**
     * @param  class-string  $resourceClass
     * @return $this
     */
    public function setIntegration(IntegrationAccount $integration, string $resourceClass, string $id): self
    {
        $this->integration = $integration;
        $this->resourceClass = $resourceClass;
        $this->id = $id;

        $this->message = "No results for resource [$resourceClass] with id [$id] in integration [$integration->account_name].";

        return $this;
    }
}
