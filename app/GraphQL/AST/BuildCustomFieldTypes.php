<?php

declare(strict_types=1);

namespace App\GraphQL\AST;

use LighthouseHelpers\Core\AddsTypes;
use GraphQL\Type\Definition\ObjectType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use GraphQL\Type\Definition\InputObjectType;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Contracts\DynamicTypeField;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;
use Mappings\Core\Mappings\Fields\Contracts\MultipleTypeField;

/**
 * There are a lot of different types for the different fields.
 * Rather than write them all out in a .graphql file, they are generated
 * programmatically here to ensure new field types are included and to avoid
 * mistakes.
 */
class BuildCustomFieldTypes
{
    use AddsTypes;

    protected array $getTypes = [];

    protected array $inputTypes = [];

    protected array $getRangeTypes = [];

    protected array $inputRangeTypes = [];

    protected array $getMultiSelectTypes = [];

    protected array $inputMultiSelectTypes = [];

    /**
     * @param  \LighthouseHelpers\Core\TypeRegistry  $registry
     */
    public function __construct(protected TypeRegistry $registry) {}

    /**
     * @return \LighthouseHelpers\Core\TypeRegistry
     */
    public function getRegistry(): TypeRegistry
    {
        return $this->registry;
    }

    public function build(): void
    {
        foreach (FieldType::getConstants() as $type) {
            $field = FieldType::fromValue($type)->newField([]);
            if ($field instanceof DynamicTypeField) {
                continue;
            }
            $fieldGetTypes = $field instanceof MultipleTypeField ? $field::possibleTypes() : [$field->graphQLType];
            $fieldInputTypes = $field instanceof MultipleTypeField ? $field::possibleInputTypes() : [$field->graphQLInputType];

            $this->getTypes = [...$this->getTypes, ...$fieldGetTypes];
            $this->inputTypes = [...$this->inputTypes, ...$fieldInputTypes];

            if ($field instanceof RangeField) {
                $this->getRangeTypes = [...$this->getRangeTypes, ...$fieldGetTypes];
                $this->inputRangeTypes = [...$this->inputRangeTypes, ...$fieldInputTypes];
            }

            if ($field instanceof MultiSelectField) {
                $this->getMultiSelectTypes = [...$this->getMultiSelectTypes, ...$fieldGetTypes];
                $this->inputMultiSelectTypes = [...$this->inputMultiSelectTypes, ...$fieldInputTypes];
            }
        }

        foreach (array_unique($this->getTypes) as $type) {
            $this->buildValueType("{$type}Value", $type);
            $this->buildListValueType("{$type}ListValue", "{$type}Value");
        }
        foreach (array_unique($this->inputTypes) as $type) {
            $typeWithoutInput = preg_replace('/Input$/', '', $type);
            $this->buildInputValueType("{$typeWithoutInput}ValueInput", $type);
            $this->buildListInputValueType("{$typeWithoutInput}ListValueInput", "{$typeWithoutInput}ValueInput");
        }
        foreach (array_unique($this->getRangeTypes) as $type) {
            $this->buildValueType("{$type}RangeValue", "{$type}Range");
            $this->buildListValueType("{$type}RangeListValue", "{$type}RangeValue");
        }
        foreach (array_unique($this->inputRangeTypes) as $type) {
            $typeWithoutInput = preg_replace('/Input$/', '', $type);
            $this->buildInputValueType("{$typeWithoutInput}RangeValueInput", "{$typeWithoutInput}RangeInput");
            $this->buildListInputValueType("{$typeWithoutInput}RangeListValueInput", "{$typeWithoutInput}RangeValueInput");
        }
        foreach (array_unique($this->getMultiSelectTypes) as $type) {
            $this->buildValueType("{$type}MultiSelectValue", $type, list: true);
            $this->buildListValueType("{$type}MultiSelectListValue", "{$type}MultiSelectValue");
        }
        foreach (array_unique($this->inputMultiSelectTypes) as $type) {
            $typeWithoutInput = preg_replace('/Input$/', '', $type);
            $this->buildInputValueType("{$typeWithoutInput}MultiSelectValueInput", $type, list: true);
            $this->buildListInputValueType("{$typeWithoutInput}MultiSelectListValueInput", "{$typeWithoutInput}MultiSelectValueInput");
        }
    }

    protected function buildValueType(string $typeName, string $valueType, bool $list = false): void
    {
        $this->register(new ObjectType($this->valueTypeOptions($typeName, $valueType, $list, [
            'label' => $this->string(nullable: true),
            'labelKey' => $this->string(nullable: true),
        ])));
    }

    protected function buildInputValueType(string $typeName, string $valueType, bool $list = false): void
    {
        $this->register(new InputObjectType($this->valueTypeOptions($typeName, $valueType, $list, [
            'label' => $this->string(nullable: true),
        ])));
    }

    protected function buildListValueType(string $typeName, string $valueType): void
    {
        $this->register(new ObjectType($this->valueTypeOptions($typeName, $valueType, true, key: 'listValue')));
    }

    protected function buildListInputValueType(string $typeName, string $valueType): void
    {
        $this->register(new InputObjectType($this->valueTypeOptions($typeName, $valueType, true, key: 'listValue')));
    }

    /**
     * @return array{name: string, fields: array<string, array{type: \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\InputType}>}
     */
    protected function valueTypeOptions(string $typeName, string $valueType, bool $list = false, array $extraFields = [], string $key = 'fieldValue'): array
    {
        return [
            'name' => $typeName,
            'fields' => [
                $key => fn () => $this->buildType($valueType, list: $list, nullable: true),
                'main' => $this->boolean(nullable: true),
                ...$extraFields,
            ],
        ];
    }

    protected function replaceInput(string $name, string $suffix): string
    {
        /** @var string $name */
        $name = preg_replace('/(Input)?$/', $suffix, $name);

        return $name;
    }
}
