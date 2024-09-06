<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Validator;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

class TimeField extends Field implements RangeField
{
    use HasRangeOption;

    public const TIME_REGEX = '/^([0-1]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/';

    public static string $type = 'TIME';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    public function validateGreaterThan(mixed $value, mixed $otherValue, string $otherField, string $attribute, Validator $validator): bool
    {
        return ! ($otherValue && $value && today()->setTimeFromTimeString($value)->lt(today()->setTimeFromTimeString($otherValue)));
    }

    /**
     * @param  "from"|"to"|null  $fromOrTo
     * @return ValidationRule[]
     */
    protected function individualRules(?string $fromOrTo = null)
    {
        return ['string', function ($attribute, $value, $fail) {
            if (! preg_match(self::TIME_REGEX, $value)) {
                $fail(trans('validation.time', $this->attributes()));
            }
        }];
    }
}
