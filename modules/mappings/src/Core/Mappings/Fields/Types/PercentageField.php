<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;

class PercentageField extends Field
{
    public static string $type = 'PERCENTAGE';

    public string $graphQLType = 'Float';

    public string $graphQLInputType = 'Float';
}
