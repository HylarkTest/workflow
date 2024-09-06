<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

class NumberField extends Field implements RangeField
{
    use HasRangeOption;

    public static string $type = 'NUMBER';

    public string $graphQLType = 'Float';

    public string $graphQLInputType = 'Float';

    /**
     * @param  "from"|"to"|null  $fromOrTo
     * @return ValidationRule[]
     */
    protected function individualRules(?string $fromOrTo = null): array
    {
        return ['numeric'];
    }
}
