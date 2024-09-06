<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

use Illuminate\Support\Str;
use Mappings\Rules\GreaterThanRule;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Trait HasRangeOption
 *
 * @mixin \Mappings\Core\Mappings\Fields\Field
 *
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
trait HasRangeOption
{
    public function graphQLType(string $prefix): string
    {
        $type = parent::graphQLType($prefix);

        if ($this->isRange()) {
            return "{$type}Range";
        }

        return $type;
    }

    public function graphQLInputType(string $prefix): string
    {
        $type = parent::graphQLInputType($prefix);

        if ($this->isRange()) {
            if (Str::endsWith($type, 'Input')) {
                $type = Str::replaceLast('Input', '', $type);
            }

            return "{$type}RangeInput";
        }

        return $type;
    }

    /**
     * @param  mixed[]  $args
     * @param  mixed  $value
     * @return array|mixed
     */
    public function resolveSingleValue($value, $args)
    {
        if ($this->isRange()) {
            return [
                'from' => filled($value[0]) ? $this->resolveIndividualValue($value[0], $args) : null,
                'to' => filled($value[1]) ? $this->resolveIndividualValue($value[1], $args) : null,
            ];
        }

        return $this->resolveIndividualValue($value, $args);
    }

    public function isRange(): bool
    {
        return $this->option('isRange', false);
    }

    public function greaterThanRule(): string
    {
        return 'gt';
    }

    public function greaterThanValidationMessage(string $attribute, mixed $otherValue): string
    {
        return trans('validation.gt.numeric', [
            'attribute' => $attribute,
            'value' => $otherValue,
        ]);
    }

    public function validateGreaterThan(mixed $value, mixed $otherValue, string $otherField, string $attribute, Validator $validator): bool
    {
        $rule = ucfirst($this->greaterThanRule());
        $method = "validate$rule";

        return ! ($otherValue && $value && ! $validator->$method($attribute, $value, [$otherField]));
    }

    public function singleAttributes(): array
    {
        return [
            ...parent::singleAttributes(),
            ...$this->rangeAttributes(),
        ];
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            ...parent::optionRules($data),
            ...$this->rangeOptionRules(),
        ];
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = parent::fieldValueRules($isCreate);
        if (! $this->isRange()) {
            $rules = array_merge($rules, $this->individualRules());
        }

        return $rules;
    }

    protected function fieldValueSubRules(bool $isCreate): array
    {
        $rules = parent::fieldValueSubRules($isCreate);
        if ($this->isRange()) {
            $rules['to'] = [...$this->individualRules('to'), 'nullable'];
            if ($this->toMustBeGreater()) {
                $rules['to'][] = $this->getGreaterThanRule();
            }
            $rules['from'] = $this->individualRules('from');
            if ($this->fromMustBeGreater()) {
                $rules['from'][] = $this->getGreaterThanRule();
            }
        }

        return $rules;
    }

    protected function toMustBeGreater(): bool
    {
        return $this->option('rules.enforceGreater', false) === 'to';
    }

    protected function fromMustBeGreater(): bool
    {
        return $this->option('rules.enforceGreater', false) === 'from';
    }

    protected function getGreaterThanRule(): ValidationRule
    {
        return new GreaterThanRule($this);
    }

    /**
     * @param  mixed  $value
     * @param  mixed|null  $originalValue
     * @return array|mixed
     */
    public function prepareForSerialization($value, $originalValue = null)
    {
        if ($this->isRange()) {
            return [
                filled($value['from'] ?? null) ? $this->serializeSinglePart($value['from'], $originalValue['from'] ?? null) : null,
                filled($value['to'] ?? null) ? $this->serializeSinglePart($value['to'], $originalValue['to'] ?? null) : null,
            ];
        }

        return $this->serializeSinglePart($value);
    }

    protected function serializeSinglePart(mixed $value, mixed $originalValue = null): mixed
    {
        return $value;
    }

    protected function resolveIndividualValue(mixed $value, array $args): mixed
    {
        return $value;
    }

    /**
     * @param  "from"|"to"|null  $fromOrTo
     * @return ValidationRule[]
     */
    protected function individualRules(?string $fromOrTo = null): array
    {
        return [];
    }

    protected function rangeAttributes(): array
    {
        return [
            'fieldValue.to' => "\"$this->name\" to",
            'fieldValue.from' => "\"$this->name\" from",
        ];
    }

    protected function rangeOptionRules(): array
    {
        return [
            'isRange' => 'bool',
            'rules.enforceGreater' => 'in:from,to,no',
        ];
    }
}
