<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;

class EmailField extends Field implements StringableField
{
    public const MAX_LENGTH = 255;

    public static string $type = 'EMAIL';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate),
            'max:'.self::MAX_LENGTH,
            'email',
        ];
    }
}
