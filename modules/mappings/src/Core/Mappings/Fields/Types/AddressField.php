<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rules\Enum;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Mappings\Fields\AddressFieldName;
use Mappings\Core\Mappings\Fields\Concerns\HasMaxLength;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class AddressField extends Field
{
    use HasMaxLength;

    public const MAX_LENGTH = 255;

    public const FIELDS = [
        AddressFieldName::LINE1,
        AddressFieldName::LINE2,
        AddressFieldName::CITY,
        AddressFieldName::STATE,
        AddressFieldName::COUNTRY,
        AddressFieldName::POSTCODE,
    ];

    public static string $type = 'ADDRESS';

    public string $graphQLType = 'Address';

    public string $graphQLInputType = 'AddressInput';

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator)
    {
        if (! $field) {
            parent::__construct($field, $translator);

            return;
        }
        if ($addressFields = $field['options']['rules']['requiredFields'] ?? false) {
            $field['options']['rules']['requiredFields'] = array_map(function ($addressField) {
                if ($addressField instanceof AddressFieldName) {
                    return $addressField;
                }

                return AddressFieldName::from($addressField);
            }, $addressFields);
        }
        if ($addressFields = $field['options']['only'] ?? false) {
            $field['options']['only'] = array_map(function ($addressField) {
                if ($addressField instanceof AddressFieldName) {
                    return $addressField;
                }

                return AddressFieldName::from($addressField);
            }, $addressFields);
        }
        parent::__construct($field, $translator);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if ($addressFields = $array['options']['rules']['requiredFields'] ?? []) {
            $array['options']['rules']['requiredFields'] = array_map(
                fn ($field) => $field instanceof AddressFieldName ? $field->name : $field,
                $addressFields
            );
        }
        if ($addressFields = $array['options']['only'] ?? []) {
            $array['options']['only'] = array_map(
                fn ($field) => $field instanceof AddressFieldName ? $field->name : $field,
                $addressFields
            );
        }

        return $array;
    }

    public function resolveOptions(): array
    {
        $options = parent::resolveOptions();

        if ($addressFields = $options['rules']['requiredFields'] ?? []) {
            $options['rules']['requiredFields'] = array_map(
                fn (AddressFieldName $field) => $field->name,
                $addressFields
            );
        }
        if ($addressFields = $options['only'] ?? []) {
            $options['only'] = array_map(
                fn (AddressFieldName $field) => $field->name,
                $addressFields
            );
        }

        return $options;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(
            parent::optionRules($data),
            static::maxOptionRules(),
            [
                'rules.requiredFields' => 'array',
                'rules.requiredFields.*' => [new Enum(AddressFieldName::class)],
                'only' => 'array',
                'only.*' => [new Enum(AddressFieldName::class)],
            ],
        );
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $rules = [];

        $max = $this->maxLength();

        $requiredFields = $this->rule('requiredFields');

        foreach (static::FIELDS as $field) {
            $lowerField = mb_strtolower($field->name);
            $rules[$lowerField] = ['nullable', 'string', "max:$max"];
            if ($requiredFields && \in_array($field, $requiredFields, false)) {
                $rules[$lowerField][] = 'required_with:{field}';
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        foreach (static::FIELDS as $field) {
            $lowerField = mb_strtolower($field->name);
            $attributes["fieldValue.$lowerField"] = "\"$this->name\" $lowerField";
        }

        return $attributes;
    }

    public function canBeSorted(): bool
    {
        return false;
    }

    public function prepareForSerialization($item, $originalValue = null)
    {
        return array_filter($item ?? []) ?: null;
    }
}
