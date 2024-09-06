<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Support\Carbon;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class DateField extends Field implements RangeField
{
    use HasRangeOption {
        optionRules as traitOptionRules;
    }

    public static string $type = 'DATE';

    public string $graphQLType = 'Date';

    public string $graphQLInputType = 'Date';

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['max.after'] = $this->translator->get(
            'validation.after',
            ['attribute' => "\"$this->name\" to", 'date' => "\"$this->name\" from"]
        );

        return $messages;
    }

    /**
     * @param  "from"|"to"|null  $fromOrTo
     * @return ValidationRule[]
     */
    public function individualRules(?string $fromOrTo = null): array
    {
        $rules = [];
        if ($before = $this->rule('before')) {
            $beforeRule = "before:$before";
            if (! $fromOrTo || $fromOrTo === 'to') {
                $rules[] = $beforeRule;
            }
        }
        if ($after = $this->rule('after')) {
            $afterRule = "after:$after";
            if (! $fromOrTo || $fromOrTo === 'from') {
                $rules[] = $afterRule;
            }
        }
        if ($fromOrTo === 'to' && ($difference = $this->maxDifferenceInSeconds()) !== null) {
            $rules[] = "max_difference:{field}.from,$difference";
        }

        return $rules;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            ...$this->traitOptionRules($data),
            'rules.before' => 'date|after:input.options.rules.after',
            'rules.after' => 'date',
        ];
    }

    public function greaterThanRule(): string
    {
        return 'after';
    }

    protected function maxDifferenceInSeconds(): ?int
    {
        if ($difference = $this->rule('maxDifference')) {
            return $difference * 24 * 60 * 60;
        }

        return null;
    }

    /**
     * @param  \Illuminate\Support\Carbon|string  $value
     * @param  \Illuminate\Support\Carbon|null  $originalValue
     */
    protected function serializeSinglePart($value, $originalValue = null): string
    {
        return \is_string($value) ? $value : $value->toDateString();
    }

    /**
     * @param  array  $args
     * @param  string|null  $value
     * @return string
     */
    protected function resolveIndividualValue($value, $args)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
