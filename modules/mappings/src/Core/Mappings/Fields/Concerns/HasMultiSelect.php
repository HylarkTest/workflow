<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

use GraphQL\Type\Definition\Type;

/**
 * @phpstan-import-type FieldDefinition from \LighthouseHelpers\Core\AddsTypes
 *
 * @mixin \Mappings\Core\Mappings\Fields\Field
 */
trait HasMultiSelect
{
    public function isMultiSelect(): bool
    {
        return $this->option('multiSelect', false);
    }

    public function graphQLInputType(string $prefix): string
    {
        $type = parent::graphQLInputType($prefix);

        if ($this->isMultiSelect()) {
            /** @var string $type */
            $type = preg_replace('/(Input)?$/i', 'MultiSelectInput', $type);
        }

        return $type;
    }

    public function graphQLType(string $prefix): string
    {
        $type = parent::graphQLType($prefix);

        if ($this->isMultiSelect()) {
            return "{$type}MultiSelect";
        }

        return $type;
    }

    /**
     * @param  mixed  $value
     * @return array|\GraphQL\Deferred|\GraphQL\Deferred[]|mixed|null[]|string|string[]|null
     *
     * @throws \Exception
     */
    public function resolveSingleValue($value, array $args)
    {
        if ($value === null) {
            return $value;
        }
        if ($this->isMultiSelect()) {
            $value = \is_array($value) && array_is_list($value) ? $value : [$value];

            return array_map(fn ($valueItem) => $this->resolveSuperSingleValue($valueItem, $args), $value);
        }

        return $this->resolveSuperSingleValue($value, $args);
    }

    protected function resolveSuperSingleValue(mixed $value, array $args): mixed
    {
        return $value;
    }

    /**
     * @param  mixed  $value
     * @param  mixed|null  $originalValue
     * @return array|mixed|string|string[]|null
     */
    public function prepareForSerialization($value, $originalValue = null)
    {
        if ($value === null) {
            return $value;
        }
        if ($this->isMultiSelect()) {
            $value = \is_array($value) && array_is_list($value) ? $value : [$value];

            return array_map(
                fn ($valueItem) => $this->serializeSuperSingleValue($valueItem),
                $value,
            );
        }

        return $this->serializeSuperSingleValue($value);
    }

    protected function serializeSuperSingleValue(mixed $value): mixed
    {
        return $value;
    }
}
