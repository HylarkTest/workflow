<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

use Illuminate\Support\Str;
use GraphQL\Type\Definition\Type;
use LighthouseHelpers\Core\AddsTypes;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;

/**
 * Class HasGraphQLDefinitions
 *
 * @method string fieldName()
 * @method bool isList()
 * @method bool isLabeled()
 */
trait HasGraphQLDefinitions
{
    use AddsTypes {
        buildType as buildGraphQLType;
    }

    /**
     * The specific type returned by the instance of this field.
     */
    public string $graphQLType;

    /**
     * The specific type used for saving an instance of this field.
     */
    public string $graphQLInputType;

    public function resolveValue(mixed $value, array $args): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($this->isList()) {
            $listValue = array_map(
                fn ($singleValue) => $this->resolveSingleLabeledValue($singleValue, $args),
                $this->getNestedListDataValue($value) ?: []
            );

            return $listValue ? ['listValue' => $listValue] : null;
        }

        return $this->resolveSingleLabeledValue($value, $args);
    }

    public function resolveNestedDataValue(mixed $value, array $args): mixed
    {
        if (! $value) {
            return $value;
        }
        if ($this->isList()) {
            return array_map(fn ($val) => $this->resolveSingleValue($this->getNestedDataValue($val), $args), $this->getNestedListDataValue($value));
        }

        return $this->resolveSingleValue($this->getNestedDataValue($value), $args);
    }

    public function serializeValue(mixed $value, mixed $originalValue = null): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($this->isList()) {
            $listValue = array_filter(
                array_map([$this, 'serializeSingleValue'], $this->getNestedListInputValue($value) ?? [], $this->getNestedListDataValue($originalValue) ?? []),
                function ($listValue) {
                    return isset($listValue[Field::VALUE]) || isset($listValue[Field::LABEL]);
                }
            );

            return $listValue ? [Field::LIST_VALUE => $listValue] : null;
        }

        return $this->serializeSingleValue($value, $originalValue);
    }

    /**
     * The full GraphQL definition of this field that will be added to any type
     * object that uses it.
     */
    public function graphQLDefinition(string $prefix): array
    {
        $type = $this->graphQLType($prefix);

        if ($this->isList()) {
            $type .= 'List';
        }

        return $this->buildGraphQLType(
            "{$type}Value",
            nullable: true,
            args: $this->arguments() ?? [],
        );
    }

    /**
     * The full GraphQL definition for input type objects.
     */
    public function graphQLInputDefinition(string $prefix): array
    {
        $type = $this->graphQLInputType($prefix);

        $suffix = $this->isList() ? 'ListValueInput' : 'ValueInput';

        if (Str::endsWith($type, 'Input')) {
            $type = Str::replaceLast('Input', '', $type);
        }
        $type = "$type$suffix";

        return $this->buildGraphQLType(
            $type,
            nullable: true,
        );
    }

    public function getNestedListDataValue(mixed $value): mixed
    {
        return $value[Field::LIST_VALUE] ?? null;
    }

    public function getNestedDataValue(mixed $value): mixed
    {
        return $value[Field::VALUE] ?? null;
    }

    public function getNestedListInputValue(mixed $value): mixed
    {
        return $value['listValue'] ?? null;
    }

    public function getNestedInputValue(mixed $value): mixed
    {
        return $value['fieldValue'] ?? null;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function resolveSingleValue($value, array $args)
    {
        return $value;
    }

    /**
     * @param  mixed  $item
     * @param  mixed  $originalValue
     * @return mixed|null
     */
    protected function serializeSingleValue($item, $originalValue)
    {
        $nestedItem = $this->getNestedInputValue($item);
        $nestedOriginalValue = $this->getNestedDataValue($originalValue);
        $isMultiSelect = $this instanceof MultiSelectField && $this->isMultiSelect();
        if (filled($nestedItem)) {
            $serialized = $this->prepareForSerialization($nestedItem, $nestedOriginalValue ?? null);
        } elseif ($isMultiSelect && is_array($item)) {
            // The array must be empty if `filled()` returns false
            $serialized = [];
        } else {
            $serialized = null;
        }

        return array_filter([
            ...($this->isLabeled() ? [Field::LABEL => $item['label'] ?? null] : []),
            Field::VALUE => $serialized,
            FIELD::IS_MAIN => $item['main'] ?? null ? 1 : null,
        ], function ($value, $key) {
            // This checks for empty arrays which should only exist for multi-select fields
            // enforced by the code above.
            if ($key === Field::VALUE && is_array($value)) {
                return true;
            }

            return filled($value);
        }, ARRAY_FILTER_USE_BOTH) ?: null;
    }

    /**
     * @param  mixed  $item
     * @param  mixed  $originalValue
     * @return mixed|null
     */
    public function prepareForSerialization($item, $originalValue = null)
    {
        return $item;
    }

    protected function resolveSingleLabeledValue(mixed $value, array $args): mixed
    {
        $extra = [];

        if ($this->isList()) {
            $extra['main'] = (bool) ($value[Field::IS_MAIN] ?? null);
        }

        // Doing it this way allows the user to enable/disable labels
        $nestedValue = $this->getNestedDataValue($value);
        if ($this->isLabeled()) {
            if ($this->isFreetextLabeled()) {
                $label = $value[Field::LABEL] ?? null;
                $labelKey = null;
            } else {
                $label = $this->option('labeled.labels.'.($value[Field::LABEL] ?? ''));
                $labelKey = $label === null ? null : ($value[Field::LABEL] ?? null);
            }

            $extra['label'] = $label;
            $extra['labelKey'] = $labelKey;
        }

        return [
            'fieldValue' => $this->resolveSingleValue($nestedValue, $args),
            ...$extra,
        ];
    }

    /**
     * An array of arguments and their types that should be included in the
     * field definition for this field.
     */
    protected function arguments(): ?array
    {
        return null;
    }

    /**
     * The type that is returned by this field
     */
    protected function graphQLType(string $prefix): string
    {
        return $this->graphQLType;
    }

    /**
     * The type to use when saving this field
     */
    protected function graphQLInputType(string $prefix): string
    {
        return $this->graphQLInputType;
    }
}
