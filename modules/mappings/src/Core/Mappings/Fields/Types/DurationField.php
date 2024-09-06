<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Validation\Validator;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\DurationFieldName;
use NunoMaduro\Collision\Exceptions\ShouldNotHappen;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

class DurationField extends Field implements RangeField
{
    use HasRangeOption {
        singleAttributes as traitSingleAttributes;
    }

    public const FIELDS = [
        DurationFieldName::MINUTES,
        DurationFieldName::HOURS,
        DurationFieldName::DAYS,
        DurationFieldName::WEEKS,
        DurationFieldName::MONTHS,
    ];

    public static string $type = 'DURATION';

    public string $graphQLType = 'Duration';

    public string $graphQLInputType = 'DurationInput';

    public function singleAttributes(): array
    {
        $attributes = $this->traitSingleAttributes();
        foreach (static::FIELDS as $field) {
            $lowerField = mb_strtolower($field->name);
            $attributes["fieldValue.$lowerField"] = "\"$this->name\" $lowerField";
            $attributes["fieldValue.from.$lowerField"] = "\"$this->name\" $lowerField";
            $attributes["fieldValue.to.$lowerField"] = "\"$this->name\" $lowerField";
        }

        return $attributes;
    }

    /**
     * @param  array  $args
     * @param  array|null  $value
     * @return array
     */
    public function resolveIndividualValue($value, $args)
    {
        return collect(self::FIELDS)
            ->map(fn ($field) => mb_strtolower($field->value))
            ->mapWithKeys(
                fn ($field) => [$field => $value[$field] ?? null]
            )->all();
    }

    public static function translateDuration(array $duration): string
    {
        $list = collect();
        $fields = array_reverse(static::FIELDS);
        foreach ($fields as $field) {
            $unit = mb_strtolower($field->value);
            $number = $duration[$unit] ?? 0;
            if ($number) {
                $list->push(((string) $number).' '.trans_choice("common.dates.units.$unit", $number));
            }
        }

        return trans_choice('common.list', $list, [
            'start' => $list->slice(0, -1)->implode(', '), 'last' => $list->last(),
        ]);
    }

    public function greaterThanValidationMessage(string $attribute, mixed $otherValue): string
    {
        return trans('validation.gt.numeric', [
            'attribute' => $attribute,
            'value' => self::translateDuration($otherValue),
        ]);
    }

    public function validateGreaterThan(mixed $value, mixed $otherValue, string $otherField, string $attribute, Validator $validator): bool
    {
        return ! ($otherValue && $value && $this->durationSize($value) < $this->durationSize($otherValue));
    }

    public function durationSize(array $duration): int
    {
        $size = 0;
        foreach ($duration as $unit => $value) {
            $size += $value * $this->unitSize($unit);
        }

        return $size;
    }

    public function toSearchable(mixed $data): mixed
    {
        $value = parent::toSearchable($data);
        if ($value === null) {
            return null;
        }
        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->durationSize($item), $value);
        }

        return $this->durationSize($value);
    }

    /**
     * @param  array|null  $item
     * @param  array|null  $originalValue
     * @return array
     */
    protected function serializeSinglePart($item, $originalValue = null)
    {
        return array_filter($item ?? [], 'filled');
    }

    protected function unitSize(string $unit): int
    {
        return match ($unit) {
            'minutes' => 1,
            'hours' => 60,
            'days' => 60 * 24,
            'weeks' => 60 * 24 * 7,
            'months' => 60 * 24 * 30,
            default => throw new ShouldNotHappen,
        };
    }
}
