<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Concerns\HasMaxLength;

class IconField extends Field
{
    use HasMaxLength;

    public const MAX_LENGTH = 50;

    public static string $type = 'ICON';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    public function canBeSorted(): bool
    {
        return false;
    }
}
