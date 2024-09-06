<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Rule;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Currency\Contracts\CurrencyRepository;
use Mappings\Core\Mappings\Fields\Concerns\HasMultiSelect;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class CurrencyField extends Field implements MultiSelectField, StringableField
{
    use HasMultiSelect;

    public static string $type = 'CURRENCY';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, protected CurrencyRepository $currency)
    {
        parent::__construct($field, $translator);
    }

    public function allowedCurrencies(): array
    {
        $only = $this->option('only');
        $allCurrencies = $this->currency->currencies();

        return $only ? array_intersect($allCurrencies, $only) : $allCurrencies;
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = parent::fieldValueRules($isCreate);

        $rules[] = Rule::in($this->allowedCurrencies());

        if ($this->isMultiSelect()) {
            $rules[] = 'array';
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = parent::messages();

        return array_merge($messages ?: [], [
            'fieldValue.in' => $this->translator->get('mappings::validation.rules.currency'),
        ]);
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        $rules = parent::optionRules($data);

        $rules['only'] = ['array', Rule::in($this->currency->currencies())];
        $rules['multiSelect'] = ['boolean'];

        return $rules;
    }

    /**
     * @param  string  $value
     * @return string
     */
    public function resolveSuperSingleValue($value, array $args)
    {
        if ($args['symbol'] ?? false) {
            return $this->currency->currency($value)->symbol();
        }

        return $value;
    }

    protected function arguments(): ?array
    {
        return ['symbol' => $this->boolean(nullable: true)];
    }
}
