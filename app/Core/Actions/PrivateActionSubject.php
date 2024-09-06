<?php

declare(strict_types=1);

namespace App\Core\Actions;

use App\Models\Action;
use Actions\Models\Contracts\ActionSubject;

interface PrivateActionSubject extends ActionSubject
{
    public function isPrivateAction(Action $action): bool;
}
