<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use LighthouseHelpers\Directives\Concerns\UsesRelationshipCursorPagination;
use Nuwave\Lighthouse\Schema\Directives\BelongsToManyDirective as BaseBelongsToManyDirective;

class BelongsToManyDirective extends BaseBelongsToManyDirective
{
    use UsesRelationshipCursorPagination;
}
