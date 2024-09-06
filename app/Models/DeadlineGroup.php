<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Models\Concerns\HasActions;
use Actions\Models\Contracts\ActionSubject;
use App\Models\Concerns\HasBaseScopedRelationships;
use Timekeeper\Models\DeadlineGroup as BaseDeadlineGroup;

/**
 * Class DeadlineGroup
 */
class DeadlineGroup extends BaseDeadlineGroup implements ActionSubject
{
    use HasActions;
    use HasBaseScopedRelationships;
}
