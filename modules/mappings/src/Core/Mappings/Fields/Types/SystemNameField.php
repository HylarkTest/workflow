<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rule;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class SystemNameField extends NameField
{
    public static string $type = 'SYSTEM_NAME';

    public function cannotRemove(): ?string
    {
        return 'The system name field cannot be removed.';
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            'labeled.freeText' => 'bool',
            'labeled.labels' => 'array',
            'labeled.labels.*' => 'string|max:100',
            'type' => [Rule::in([
                'NAME',
                'FIRST_NAME',
                'LAST_NAME',
                'FULL_NAME',
                'PREFERRED_NAME',
                'NICKNAME',
            ])],
        ];
    }

    public function rules(bool $isCreate): array
    {
        return [
            ...parent::rules($isCreate),
            'fieldValue' => [$isCreate ? 'required' : 'filled', 'max:100'],
        ];
    }
}
