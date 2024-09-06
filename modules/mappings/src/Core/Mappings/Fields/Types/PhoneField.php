<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;

class PhoneField extends Field
{
    public static string $type = 'PHONE';

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
}
