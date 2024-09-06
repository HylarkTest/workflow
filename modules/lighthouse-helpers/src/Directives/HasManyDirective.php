<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use LighthouseHelpers\Directives\Concerns\UsesRelationshipCursorPagination;
use Nuwave\Lighthouse\Schema\Directives\HasManyDirective as BaseHasManyDirective;

class HasManyDirective extends BaseHasManyDirective
{
    use UsesRelationshipCursorPagination;
}
