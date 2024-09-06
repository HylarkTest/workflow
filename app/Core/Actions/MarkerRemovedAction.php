<?php

declare(strict_types=1);

namespace App\Core\Actions;

use App\Models\Action;

class MarkerRemovedAction extends Action
{
    public function changes(): ?array
    {
        return null;
    }
}
