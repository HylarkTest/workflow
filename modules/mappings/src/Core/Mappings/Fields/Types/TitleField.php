<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;

class TitleField extends Field implements StringableField
{
    public static string $type = 'TITLE';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate),
            'max:50',
        ];
    }

    public function canBeSorted(): bool
    {
        return false;
    }
}
