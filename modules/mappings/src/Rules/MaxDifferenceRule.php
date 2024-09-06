<?php

declare(strict_types=1);

namespace Mappings\Rules;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class MaxDifferenceRule
{
    use ValidatesAttributes;

    protected Validator $validator;

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function validate(string $attribute, mixed $value, array $parameters, Validator $validator): bool
    {
        $this->validator = $validator;

        $this->requireParameterCount(2, $parameters, 'maxDifference');

        $minValue = Carbon::parse($value)->subSeconds($parameters[1]);

        return $this->compareDates($attribute, $minValue, $parameters, '<');
    }

    /**
     * @param  string  $attribute
     * @return null
     */
    protected function getDateFormat($attribute)
    {
        return null;
    }

    protected function getValue(string $attribute): mixed
    {
        return Arr::get($this->validator->getData(), $attribute);
    }
}
