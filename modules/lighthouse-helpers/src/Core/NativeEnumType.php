<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use GraphQL\Type\Definition\EnumType;

class NativeEnumType extends EnumType
{
    public const DEPRECATED_PHPDOC_TAG = '@deprecated';

    /**
     * @var class-string
     */
    protected $enumClass;

    /**
     * @param  class-string  $enumClass
     */
    public function __construct(string $enumClass, ?string $name = null)
    {
        if (! enum_exists($enumClass)) {
            throw new \InvalidArgumentException("Must pass an enum, got {$enumClass}.");
        }

        $this->enumClass = $enumClass;

        $values = [];

        foreach ($enumClass::cases() as $enum) {
            $values[$enum->name] = [
                'value' => $enum->value ?? $enum->name,
            ];
        }

        parent::__construct([
            'name' => $name ?? class_basename($enumClass),
            'values' => $values,
        ]);
    }

    /**
     * Overwrite the native EnumType serialization, as this class does not hold plain values.
     *
     * @param  mixed  $value
     */
    public function serialize($value): string
    {
        if (! is_a($value, $this->enumClass)) {
            $value = $this->enumClass::from($value);
        }

        return $value->value;
    }
}
