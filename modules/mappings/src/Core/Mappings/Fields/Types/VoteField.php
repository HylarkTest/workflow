<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;

class VoteField extends Field
{
    public static string $type = 'VOTE';

    public string $graphQLType = 'Int';

    public string $graphQLInputType = 'Int';

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate),
            'integer',
        ];
    }

    /**
     * @param  numeric|null  $item
     * @param  numeric|null  $originalValue
     * @return int
     */
    public function prepareForSerialization($item, $originalValue = null)
    {
        return (int) $originalValue + (int) $item;
    }
}
