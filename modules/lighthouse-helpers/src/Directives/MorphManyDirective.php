<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use LighthouseHelpers\Directives\Concerns\UsesRelationshipCursorPagination;
use Nuwave\Lighthouse\Schema\Directives\MorphManyDirective as BaseMorphManyDirective;

class MorphManyDirective extends BaseMorphManyDirective
{
    use UsesRelationshipCursorPagination;
}
