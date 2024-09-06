<?php

declare(strict_types=1);

namespace App\Core\Imports;

use Illuminate\Support\Carbon;
use PHPStan\ShouldNotHappenException;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Contracts\Validation\Validator;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;

class FieldsImporter
{
    public const IMPORTABLE_FIELD_TYPES = [
        'BOOLEAN',
        'CURRENCY',
        'DATE',
        'DATE_TIME',
        'TIME',
        'EMAIL',
        'NUMBER',
        'LINE',
        'NAME',
        'PARAGRAPH',
        'PHONE',
        'RATING',
        'SYSTEM_NAME',
        'URL',
    ];

    public static function canImportField(Field $field): bool
    {
        if ($field instanceof MultiSelectField && $field->isMultiSelect()) {
            return false;
        }
        if ($field instanceof RangeField && $field->isRange()) {
            return false;
        }

        return in_array($field->type()->value, self::IMPORTABLE_FIELD_TYPES, true);
    }

    public static function getFieldValidator(null|Carbon|string $value, Field $field, ?string $dateFormat): Validator
    {
        $value = is_string($value) ? trim($value) : $value;
        if (! $value) {
            return validator()->make([], []);
        }

        if ($dateFormat && is_string($value)) {
            $phpFormats = self::phpFormatsFromDateFormat($dateFormat);
            $dateRule = function ($attribute, $value, $fail) use ($phpFormats) {
                foreach ($phpFormats as $phpFormat) {
                    if (Carbon::canBeCreatedFromFormat($value, $phpFormat)) {
                        return;
                    }
                }
                $fail(trans('validation.date', ['attribute' => $attribute]));
            };
        } else {
            $dateRule = 'date';
        }

        $rules = match ($field->type()->value) {
            FieldType::EMAIL()->value,
            FieldType::LINE()->value,
            FieldType::NAME()->value,
            FieldType::PARAGRAPH()->value,
            FieldType::PHONE()->value,
            FieldType::CURRENCY()->value,
            FieldType::TIME()->value,
            FieldType::NUMBER()->value,
            FieldType::SYSTEM_NAME()->value,
            FieldType::RATING()->value,
            FieldType::URL()->value => $field->fieldValueRules(true),
            FieldType::DATE()->value,
            FieldType::DATE_TIME()->value => [$dateRule, ...$field->fieldValueRules(true)],
            FieldType::BOOLEAN()->value => ['boolean'],
            default => throw new ShouldNotHappenException('Field type is not importable'),
        };

        return validator(['value' => $value], ['value' => $rules]);
    }

    public static function importToField(null|Carbon|string $value, Field $field, ?string $dateFormat): mixed
    {
        $value = is_string($value) ? trim($value) : $value;
        if (! $value) {
            return null;
        }

        return match ($field->type()->value) {
            FieldType::EMAIL()->value,
            FieldType::LINE()->value,
            FieldType::NAME()->value,
            FieldType::PARAGRAPH()->value,
            FieldType::PHONE()->value,
            FieldType::CURRENCY()->value,
            FieldType::TIME()->value,
            FieldType::NUMBER()->value,
            FieldType::SYSTEM_NAME()->value,
            FieldType::RATING()->value,
            FieldType::URL()->value => $field->prepareForSerialization($value),
            FieldType::BOOLEAN()->value => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            FieldType::DATE()->value,
            FieldType::DATE_TIME()->value => $field->prepareForSerialization(static::parseDateValue($value, $dateFormat)),
            default => throw new ShouldNotHappenException('Field type is not importable'),
        };
    }

    protected static function parseDateValue(Carbon|string $value, ?string $dateFormat): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (! $dateFormat) {
            return Carbon::parse($value);
        }
        $phpFormats = self::phpFormatsFromDateFormat($dateFormat);
        foreach ($phpFormats as $phpFormat) {
            if (Carbon::canBeCreatedFromFormat($value, $phpFormat)) {
                /** @phpstan-ignore-next-line */
                return Carbon::createFromFormat($phpFormat, $value);
            }
        }
        throw new ShouldNotHappenException('Date value is not in the expected format');
    }

    public static function possibleDateFormats(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }
        $formats = ['y/m/d', 'd/m/y', 'm/d/y'];
        $possibleFormats = [];
        foreach ($formats as $format) {
            foreach (self::phpFormatsFromDateFormat($format) as $phpFormat) {
                if (Carbon::canBeCreatedFromFormat($value, $phpFormat)) {
                    $possibleFormats[] = $format;
                    break;
                }
            }
        }

        return $possibleFormats;
    }

    /**
     * @return array{string, string, string, string}
     */
    protected static function phpFormatsFromDateFormat(string $format): array
    {
        $shortYearFormat = str_replace('/', '#', $format);
        $longYearFormat = str_replace('y', 'Y', $shortYearFormat);

        return [
            $shortYearFormat,
            $longYearFormat,
            "$shortYearFormat H:i:s",
            "$longYearFormat H:i:s",
        ];
    }
}
