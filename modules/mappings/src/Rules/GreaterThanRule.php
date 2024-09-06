<?php

declare(strict_types=1);

namespace Mappings\Rules;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;

class GreaterThanRule implements DataAwareRule, ValidationRule, ValidatorAwareRule
{
    /**
     * @var array{ to: mixed, from: mixed }
     */
    protected array $data;

    protected Validator $validator;

    public function __construct(protected RangeField $field) {}

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $thisKey = Str::contains($attribute, 'to') ? 'to' : 'from';
        $otherKey = $thisKey === 'to' ? 'from' : 'to';
        $otherField = str_replace($thisKey, $otherKey, $attribute);
        $otherValue = Arr::get($this->data, $otherField);

        if (! $this->field->validateGreaterThan($value, $otherValue, $otherField, $attribute, $this->validator)) {
            $fail($this->field->greaterThanValidationMessage(
                $this->field->attributes()["fieldValue.$thisKey"],
                $otherValue
            ));
        }
    }

    /**
     * @param  array{ to: mixed, from: mixed }  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
