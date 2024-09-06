<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rules\Enum;
use Mappings\Core\Mappings\Fields\SalaryPeriod;

/**
 * @phpstan-type SalaryFieldOptionsArray array{
 *     amount: int|float|array{
 *         from: int|float,
 *         to: int|float,
 *     },
 *     period?: string,
 *     currency?: string,
 * }
 * @phpstan-type SalaryFieldOptions null|int|float|SalaryFieldOptionsArray
 *
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class SalaryField extends MoneyField
{
    public static string $type = 'SALARY';

    public string $graphQLType = 'Salary';

    public string $graphQLInputType = 'SalaryInput';

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            ...parent::optionRules($data),
            'period' => [new Enum(SalaryPeriod::class)],
        ];
    }

    public function getPeriod(mixed $value): string
    {
        return $this->option('period') ?: value($value);
    }

    /**
     * @param  mixed[]  $args
     * @param  mixed  $value
     * @return array|mixed
     */
    public function resolveSingleValue($value, $args)
    {
        $resolvedValue = parent::resolveSingleValue($value, $args);
        if ($this->isRange()) {
            $resolvedValue['period'] = $this->getPeriod(fn () => $value[3]);
        } else {
            $resolvedValue['period'] = $this->getPeriod(fn () => $value['period']);
        }

        return $resolvedValue;
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $rules = parent::fieldValueSubRules($isCreate);

        if ($this->hasVariablePeriod()) {
            $rules['period'] = ['required_with:{field}', new Enum(SalaryPeriod::class)];
        }

        return $rules;
    }

    public function canBeSorted(): bool
    {
        return parent::canBeSorted()
            && ! $this->hasVariablePeriod();
    }

    protected function hasVariablePeriod(): bool
    {
        return ! $this->option('period');
    }

    protected function hasExtraValues(): bool
    {
        return $this->hasVariableCurrency() || $this->hasVariablePeriod();
    }

    protected function singleAttributes(): array
    {
        return [
            ...parent::singleAttributes(),
            'fieldValue.period' => 'period',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'fieldValue.period.required_with' => trans('validation.required', ['attribute' => 'period']),
        ];
    }

    /**
     * @param  SalaryFieldOptions  $value
     * @param  SalaryFieldOptions  $originalValue
     * @return array|mixed
     */
    public function prepareForSerialization($value, $originalValue = null)
    {
        $hasExtraValues = $this->hasExtraValues();
        if ($this->isRange()) {
            $amount = $hasExtraValues ? $value['amount'] ?? null : $value;
            $range = [
                filled($amount['from'] ?? null) ? $this->serializeSinglePart($amount['from']) : null,
                filled($amount['to'] ?? null) ? $this->serializeSinglePart($amount['to']) : null,
            ];
            if (! $this->option('currency')) {
                /** @phpstan-ignore-next-line If the option is not there then `currency` must exist in the array */
                $range[2] = $value['currency'];
            }
            if (! $this->option('period')) {
                /** @phpstan-ignore-next-line If the option is not there then `period` must exist in the array */
                $range[3] = $value['period'];
            }

            return $range;
        }

        if (\is_array($value)) {
            if ($this->option('currency')) {
                unset($value['currency']);
            }
            if ($this->option('period')) {
                unset($value['period']);
            }
        }
        if (! $this->hasExtraValues()) {
            $value = $value['amount'] ?? null;
        }

        return $this->serializeSinglePart($value);
    }
}
