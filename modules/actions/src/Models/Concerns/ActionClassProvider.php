<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

interface ActionClassProvider
{
    public function getActionClass(): string;
}
