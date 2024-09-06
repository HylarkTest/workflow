<?php

declare(strict_types=1);

namespace App\Models;

use LighthouseHelpers\Concerns\HasGlobalId;
use Timekeeper\Models\Deadline as BaseDeadline;
use App\Models\Concerns\HasBaseScopedRelationships;

/**
 * Class Deadline
 */
class Deadline extends BaseDeadline
{
    use HasBaseScopedRelationships;
    use HasGlobalId;
}
