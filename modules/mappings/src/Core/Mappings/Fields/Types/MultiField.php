<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Mappings\Fields\FieldResolver;
use Mappings\Core\Mappings\Fields\FieldCollection;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Mappings\Core\Mappings\Fields\Contracts\DynamicTypeField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class MultiField extends Field implements DynamicTypeField
{
    public static string $type = 'MULTI';

    protected FieldCollection $fields;

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator)
    {
        $fields = $field['options']['fields'] ?? [];
        // For some reason the validation messes with the order
        ksort($fields);

        $this->fields = FieldCollection::makeCollection($fields);

        $field['options']['fields'] = $this->fields->toArray();

        parent::__construct($field, $translator);
    }

    public function fields(): FieldCollection
    {
        return $this->fields ?? ($this->fields = FieldCollection::makeCollection($this->option('fields')));
    }

    public function resolveOptions(): array
    {
        $options = parent::resolveOptions();

        return [
            ...$options,
            'fields' => $this->fields->map(function (Field $field) {
                return [
                    ...$field->toArray(),
                    'options' => $field->resolveOptions() ?: new \stdClass,
                ];
            })->all(),
        ];
    }

    public function registerDynamicFields(string $prefix): void
    {
        $prefix .= ucfirst($this->apiName);

        /** @var \Illuminate\Support\Collection<int, DynamicTypeField> $dynamicFields */
        $dynamicFields = $this->fields()->filter(fn ($field) => $field instanceof DynamicTypeField);

        $dynamicFields->each(fn (DynamicTypeField $field) => $field->registerDynamicFields($prefix));

        $this->registerLazyObject("{$prefix}Multi", fn () => [
            'fields' => fn () => $this->fields()->graphQLData($prefix)->toArray(),
        ]);

        $this->registerLazyInput("{$prefix}MultiInput", fn () => [
            'fields' => fn () => $this->fields()->graphQLInputFields($prefix)->toArray(),
        ]);

        $this->registerLazyObject("{$prefix}MultiValue", fn () => [
            'fields' => fn () => [
                'label' => $this->string(nullable: true),
                'labelKey' => $this->string(nullable: true),
                'fieldValue' => $this->buildGraphQLType("{$prefix}Multi", nullable: true),
                'main' => $this->boolean(nullable: true),
            ],
        ]);

        $this->registerLazyInput("{$prefix}MultiValueInput", fn () => [
            'fields' => fn () => [
                'label' => $this->string(nullable: true),
                'labelKey' => $this->string(nullable: true),
                'fieldValue' => $this->buildInputType("{$prefix}MultiInput", nullable: true),
                'main' => $this->boolean(nullable: true),
            ],
        ]);

        if ($this->isList()) {
            $this->registerLazyObject("{$prefix}MultiListValue", fn () => [
                'fields' => fn () => [
                    'listValue' => $this->buildGraphQLType("{$prefix}MultiValue", list: true, nullable: true),
                ],
            ]);
            $this->registerLazyInput("{$prefix}MultiListValueInput", fn () => [
                'fields' => fn () => [
                    'listValue' => $this->buildInputType("{$prefix}MultiValueInput", list: true, nullable: true),
                ],
            ]);
        }
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        /** @var array<string, mixed> $fieldData */
        $fieldData = $data['options']['fields'] ?? [];

        /** @var \Illuminate\Support\Collection<string, array<int, string|\Illuminate\Validation\Rule>|string> $rules */
        $rules = Collection::make($fieldData)->flatMap(
            static function ($fieldInfo, $index) {
                if (! \is_array($fieldInfo)) {
                    return [];
                }
                try {
                    $field = FieldType::fromValue($fieldInfo['type'])->newField([]);
                } catch (InvalidEnumMemberException $e) {
                    return [];
                }
                $rules = $field->optionRules(['field' => $fieldInfo]);

                $keyPrefix = 'fields.'.$index.'.options';

                return Collection::make($rules)->mapWithKeys(static function ($rules, $key) use ($keyPrefix) {
                    $replaceWildCards = fn ($ruleString) => is_string($ruleString) || $ruleString instanceof \Stringable
                        ? str_replace('{field}', "{field}.$keyPrefix", (string) $ruleString)
                        : $ruleString;

                    $rules = is_array($rules) ? array_map($replaceWildCards, $rules) : $replaceWildCards($rules);

                    return ["$keyPrefix.$key" => $rules];
                });
            }
        )->merge(parent::optionRules($data));

        /** @phpstan-ignore-next-line Not sure how to make the types match */
        return $rules->merge([
            'fields' => 'array|required',
            'fields.*.id' => [],
            'fields.*.apiName' => 'filled|string|max:50|api_name',
            'fields.*.type' => ['required', Rule::in(Arr::where(FieldType::getKeys(), fn ($key) => $key !== 'MULTI'))],
            'fields.*.name' => 'filled|string|max:50',
            'fields.*.meta' => 'array|nullable',
            'fields.*.createdAt' => 'date',
            'fields.*.updatedAt' => 'date',
        ])->toArray();
    }

    public function optionAttributes(array $data): array
    {
        /** @var array<string, mixed> $fieldData */
        $fieldData = $data['options']['fields'] ?? [];

        return Collection::make($fieldData)->mapWithKeys(
            function ($fieldInfo, $index) {
                if (! \is_array($fieldInfo)) {
                    return [];
                }
                try {
                    $field = FieldType::fromValue($fieldInfo['type'])->newField([]);
                } catch (InvalidEnumMemberException $e) {
                    return ["options.fields.$index" => $this->translator->get('mappings::validation.attributes.field')];
                }

                return ["options.fields.$index" => array_merge_recursive(
                    $this->translator->get('mappings::validation.attributes.field'),
                    $field->optionAttributes($fieldInfo)
                )];
            }
        )->merge(['options.fields' => $this->translator->get('mappings::validation.attributes.input.options.fields')])->all();
    }

    public function optionMessages(array $data): array
    {
        /** @var array<string, mixed> $fieldData */
        $fieldData = $data['options']['fields'] ?? [];

        return Collection::make($fieldData)->mapWithKeys(
            function ($fieldInfo, $index) {
                if (! \is_array($fieldInfo)) {
                    return [];
                }
                try {
                    $field = FieldType::fromValue($fieldInfo['type'])->newField([]);
                } catch (InvalidEnumMemberException $e) {
                    return [];
                }

                return ["options.fields.$index" => array_merge_recursive(
                    $this->translator->get('mappings::validation.field_options.input'),
                    $field->optionMessages($fieldInfo)
                )];
            }
        )->all();
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        return [
            ...$array,
            'options' => [
                ...$array['options'],
                'fields' => array_map(function ($field) {
                    return [
                        ...$field,
                        // Options should be objects when converted to JSON.
                        // That is handled by lighthouse for most fields, but
                        // it does not work for multi fields.
                        'options' => ($field['options'] ?? null) ?: new \stdClass,
                    ];
                }, $array['options']['fields']),
            ],
        ];
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $rules = parent::fieldValueSubRules($isCreate);
        foreach ($this->fields as $field) {
            $rules[$field->apiName] = $field->rules($isCreate);
        }

        return $this->dot($rules);
    }

    public function resolveSingleValue($value, array $args): FieldResolver
    {
        return new FieldResolver($value, $this->fields());
    }

    public function canBeSorted(): bool
    {
        return false;
    }

    public function updateOptions(array $options = []): void
    {
        $newFields = $options['fields'];
        $fields = $this->fields();
        $fieldIds = $fields->toBase()->pluck('id');
        foreach ($newFields as $newField) {
            $newField['type'] = FieldType::fromValue($newField['type']);
            if (isset($newField['id']) && $fieldIds->contains($newField['id'])) {
                $fields->changeItem($newField['id'], $newField, new Item);
                $fieldIds->forget($fieldIds->search($newField['id']));
            } else {
                $fields->addItem($newField, new Item);
            }
        }
        foreach ($fieldIds as $fieldId) {
            $fields->removeItem($fieldId, new Item);
        }
        parent::updateOptions(Arr::except($options, 'fields'));

        $this->options = $this->resolveOptions();
    }

    public function prepareForSerialization(mixed $data, mixed $originalData = null)
    {
        if (! $data) {
            return null;
        }
        $fields = $this->fields;

        return $fields->mapWithKeys(static function (Field $field) use ($data, $originalData) {
            $originalValue = Arr::get($originalData ?: [], $field->id());
            if (\array_key_exists($field->apiName, $data)) {
                return [$field->id() => $field->serializeValue($data[$field->apiName], $originalValue)];
            }

            return [$field->id() => $originalValue];
        })->all();
    }

    protected function graphQLType(string $prefix): string
    {
        $prefix .= ucfirst($this->apiName);

        return "{$prefix}Multi";
    }

    protected function graphQLInputType(string $prefix): string
    {
        return $this->graphQLType($prefix).'Input';
    }

    protected function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (\is_array($value) && ! empty($value) && ! array_is_list($value)) {
                $results = [
                    ...$results,
                    ...$this->dot($value, $prepend.$key.'.'),
                ];
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    protected function singleAttributes(): array
    {
        $attributes = parent::singleAttributes();

        foreach ($this->fields as $field) {
            $fieldAttributes = $field->attributes();
            $attributes['fieldValue.'.$field->fieldName()] = $fieldAttributes;
        }

        return $this->dot($attributes);
    }
}
