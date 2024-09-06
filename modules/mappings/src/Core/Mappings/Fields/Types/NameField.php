<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rule;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class NameField extends Field implements StringableField
{
    public static string $type = 'NAME';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        $rules = parent::optionRules($data);

        return array_merge($rules, [
            'type' => [Rule::in([
                'NAME',
                'FIRST_NAME',
                'LAST_NAME',
                'FULL_NAME',
                'PREFERRED_NAME',
                'NICKNAME',
            ])],
        ]);
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate),
            'max:255',
        ];
    }
}
