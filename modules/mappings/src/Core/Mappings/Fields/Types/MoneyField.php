<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rule;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Currency\Contracts\CurrencyRepository;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class MoneyField extends Field implements RangeField
{
    use HasRangeOption {
        optionRules as traitOptionRules;
        singleAttributes as traitSingleAttributes;
    }

    public static string $type = 'MONEY';

    public string $graphQLType = 'Money';

    public string $graphQLInputType = 'MoneyInput';

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, protected CurrencyRepository $currency)
    {
        parent::__construct($field, $translator);
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            ...$this->traitOptionRules($data),
            'currency' => [Rule::in([false, ...$this->currency->currencies()])],
        ];
    }

    public function getCurrency(mixed $value): string
    {
        return $this->option('currency') ?: value($value);
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
                'currency' => $this->getCurrency(fn () => $value[2]),
                'amount' => [
                    'from' => $value[0],
                    'to' => $value[1],
                ],
            ];
        }

        $currency = $this->getCurrenCy(fn () => $value['currency']);
        if ($this->hasExtraValues()) {
            return [
                'currency' => $currency,
                ...$value,
            ];
        }

        return [
            'amount' => $value,
            'currency' => $currency,
        ];
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $rules = [
            'amount' => ['required_with:{field}'],
        ];
        if ($this->isRange()) {
            $rules['amount.to'] = ['nullable', 'numeric', 'required_unless_filled:{field}.amount.from'];
            if ($this->toMustBeGreater()) {
                $rules['amount.to'][] = $this->getGreaterThanRule();
            }
            $rules['amount.from'] = ['nullable', 'numeric', 'required_unless_filled:{field}.amount.to'];
            if ($this->fromMustBeGreater()) {
                $rules['amount.from'][] = $this->getGreaterThanRule();
            }
        } else {
            $rules['amount'][] = 'numeric';
        }
        if ($this->hasVariableCurrency()) {
            $allCurrencies = $this->currency->currencies();
            $rules['currency'] = [Rule::in($allCurrencies), 'required_with:{field}'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'fieldValue.amount.to.required_unless_filled' => trans('validation.custom.fields.money.amount.required_unless_filled'),
            'fieldValue.amount.from.required_unless_filled' => trans('validation.custom.fields.money.amount.required_unless_filled'),
            'fieldValue.amount.required_with' => trans('validation.custom.fields.money.amount.'.($this->isRange() ? 'required_unless_filled' : 'required_with')),
        ];
    }

    public function toSearchable(mixed $data): mixed
    {
        $data = parent::toSearchable($data);
        if (\is_array($data) && array_is_list($data)) {
            return array_map(fn ($value) => $value['amount'], $data);
        }

        return is_array($data) ? $data['amount'] ?? null : $data;
    }

    public function canBeSorted(): bool
    {
        return parent::canBeSorted()
            && ! $this->hasVariableCurrency();
    }

    protected function singleAttributes(): array
    {
        return [
            ...$this->traitSingleAttributes(),
            'fieldValue.currency' => 'currency',
            'fieldValue.amount' => 'amount',
            'fieldValue.amount.from' => 'from',
            'fieldValue.amount.to' => 'to',
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed|null  $originalValue
     * @return array|mixed
     */
    public function prepareForSerialization($value, $originalValue = null)
    {
        if ($this->isRange()) {
            $amount = $value['amount'] ?? null;
            $range = [
                filled($amount['from'] ?? null) ? $this->serializeSinglePart($amount['from']) : null,
                filled($amount['to'] ?? null) ? $this->serializeSinglePart($amount['to']) : null,
            ];
            if ($this->hasVariableCurrency()) {
                $range[] = $value['currency'];
            }

            return $range;
        }

        if (! $this->hasVariableCurrency()) {
            $value = $value['amount'] ?? null;
        }

        return $this->serializeSinglePart($value);
    }

    protected function hasVariableCurrency(): bool
    {
        return ! $this->option('currency');
    }

    protected function hasExtraValues(): bool
    {
        return $this->hasVariableCurrency();
    }
}
