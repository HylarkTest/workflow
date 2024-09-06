<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

interface ActionPerformer
{
    public function getActionPerformerName(): ?string;
}
