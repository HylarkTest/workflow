<?php

declare(strict_types=1);

namespace App\Core\Mappings;

use App\Models\Location;
use Mappings\Models\CategoryItem;
use http\Exception\RuntimeException;
use Mappings\Core\Currency\Currency;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\MoneyField;
use Mappings\Core\Mappings\Fields\Types\MultiField;
use Mappings\Core\Documents\Contracts\ImageRepository;
use Mappings\Core\Mappings\Fields\Types\DurationField;
use Mappings\Core\Documents\Contracts\DocumentRepository;

class FieldActionFormatter
{
    // Using these keys to save space in the DB
    public const NAME = '_n';

    public const TYPE = '_t';

    public const OPTION_MASK = '_o';

    public const VAL = '_v';

    public const IS_LABELED = 1;

    public const IS_LIST = 2;

    public const IS_MULTI_SELECT = 4;

    public const IS_RANGE = 8;

    public const KEY = '_k';

    public const LABEL = '_l';

    public const FIELD_VALUE = '_v';

    public const MAIN = '_m';

    public function formatFieldValueForAction(mixed $value, Field $field, bool $withFieldInfo = true): mixed
    {
        if (! $withFieldInfo) {
            return $this->formatValue($value, $field, false);
        }
        $optionMask = 0;
        if (filled($value)) {
            if ($field->isLabeled()) {
                $optionMask |= self::IS_LABELED;
            }
            if ($field->isList()) {
                $optionMask |= self::IS_LIST;
            }
            if ($field->option('multiSelect')) {
                $optionMask |= self::IS_MULTI_SELECT;
            }
            if ($field->option('isRange', false)) {
                $optionMask |= self::IS_RANGE;
            }
        }

        return [
            self::NAME => $field->name,
            self::TYPE => $field->type()->value,
            self::OPTION_MASK => $optionMask,
            self::VAL => $this->formatValue($value, $field),
        ];
    }

    public function resolveValue(mixed $value, string $type, int $optionMask): string
    {
        if (blank($value)) {
            return '';
        }

        if ($optionMask & self::IS_LIST) {
            return implode(\PHP_EOL, array_filter(
                array_map(
                    fn ($val) => $this->resolveSingleValue($val, $type, $optionMask), $value
                )
            ));
        }

        return $this->resolveSingleValue($value, $type, $optionMask);
    }

    public function resolveNestedValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            FieldType::LINE()->value,
            FieldType::PARAGRAPH()->value,
            FieldType::SYSTEM_NAME()->value,
            FieldType::EMAIL()->value,
            FieldType::CURRENCY()->value,
            FieldType::NAME()->value,
            FieldType::DATE()->value,
            FieldType::NUMBER()->value,
            FieldType::DATE_TIME()->value,
            FieldType::SELECT()->value,
            FieldType::INTEGER()->value,
            FieldType::TITLE()->value,
            FieldType::FILE()->value,
            FieldType::IMAGE()->value,
            FieldType::URL()->value,
            FieldType::ICON()->value,
            FieldType::LOCATION()->value,
            FieldType::TIME()->value,
            FieldType::MONEY()->value,
            FieldType::SALARY()->value,
            FieldType::VOTE()->value,
            FieldType::PHONE()->value,
            FieldType::CATEGORY()->value => $value,
            FieldType::PERCENTAGE()->value => "$value%",
            FieldType::RATING()->value => isset($value[0], $value[1]) ? "$value[0]/$value[1]" : null,
            FieldType::FORMATTED()->value => FieldType::FORMATTED()->newField([])->resolveSingleValue($value, ['plaintext' => true]),
            FieldType::DURATION()->value => $value ? DurationField::translateDuration($value) : null,
            FieldType::BOOLEAN()->value => trans('common.'.($value ? 'true' : 'false')),
            FieldType::ADDRESS()->value => implode("\n", array_filter([
                $value['line1'] ?? null,
                $value['line2'] ?? null,
                $value['city'] ?? null,
                $value['state'] ?? null,
                $value['country'] ?? null,
                $value['postcode'] ?? null,
            ])),
            default => throw new RuntimeException('No definition for field type')
        };
    }

    public function formatValue(mixed $value, Field $field, bool $withFieldInfo = true): mixed
    {
        if (blank($value)) {
            return $value;
        }
        if ($field->isList()) {
            return array_map(fn ($val) => $this->formatSingleValue($val, $field, $withFieldInfo), $value[Field::LIST_VALUE]);
        }

        return $this->formatSingleValue($value, $field, $withFieldInfo);
    }

    protected function formatSingleValue(mixed $value, Field $field, bool $withFieldInfo = true): mixed
    {
        return array_filter([
            self::KEY => $field->isFreetextLabeled() ? null : $value[Field::LABEL] ?? null,
            self::LABEL => isset($value[Field::LABEL]) ? $field->option('labeled.labels.'.$value[Field::LABEL], $value[Field::LABEL]) : null,
            self::MAIN => $value[Field::IS_MAIN] ?? null ? 1 : null,
            self::FIELD_VALUE => \array_key_exists(Field::VALUE, $value)
                ? $this->formatNestedValue($value[Field::VALUE], $field, $withFieldInfo)
                : null,
        ], 'filled');
    }

    protected function formatNestedValue(mixed $value, Field $field, bool $withFieldInfo = true): mixed
    {
        if (\is_array($value) && array_is_list($value)) {
            if ($field->option('multiSelect') || $field->option('isRange')) {
                if ($field->type()->is(FieldType::MONEY()) || $field->type()->is(FieldType::SALARY())) {
                    /** @var \Mappings\Core\Mappings\Fields\Types\MoneyField|\Mappings\Core\Mappings\Fields\Types\SalaryField $field */
                    return $this->serializeMoney($value, $field);
                }

                return array_map(fn ($val) => $this->formatNestedValue($val, $field, $withFieldInfo), $value);
            }
        }

        return match ($field->type()->value) {
            FieldType::LINE()->value,
            FieldType::PARAGRAPH()->value,
            FieldType::SYSTEM_NAME()->value,
            FieldType::DATE()->value,
            FieldType::ICON()->value,
            FieldType::EMAIL()->value,
            FieldType::DATE_TIME()->value,
            FieldType::TITLE()->value,
            FieldType::URL()->value,
            FieldType::DURATION()->value,
            FieldType::NAME()->value,
            FieldType::NUMBER()->value,
            FieldType::FORMATTED()->value,
            FieldType::VOTE()->value,
            FieldType::TIME()->value,
            FieldType::PERCENTAGE()->value,
            FieldType::INTEGER()->value,
            FieldType::PHONE()->value,
            FieldType::ADDRESS()->value,
            FieldType::CURRENCY()->value => $value,
            FieldType::BOOLEAN()->value => (int) $value,
            FieldType::RATING()->value => [$value, $field->rule('max', 5)],
            FieldType::FILE()->value => (static function ($value) {
                try {
                    return resolve(DocumentRepository::class)->find($value)->filename();
                } catch (\Exception $e) {
                    return 'Unknown';
                }
            })($value),
            FieldType::IMAGE()->value => (static function ($value) {
                try {
                    return resolve(ImageRepository::class)->find($value['image'])->filename();
                } catch (\Exception $e) {
                    return 'Unknown';
                }
            })($value),
            /** @phpstan-ignore-next-line  */
            FieldType::CATEGORY()->value => CategoryItem::query()->find($value)?->name,
            /** @phpstan-ignore-next-line  */
            FieldType::LOCATION()->value => Location::query()->find($value)?->name,
            FieldType::SELECT()->value => $field->option("valueOptions.$value", $value),
            /** @phpstan-ignore-next-line We know the field is a MoneyField class */
            FieldType::MONEY()->value => $this->serializeMoney($value, $field),
            /** @phpstan-ignore-next-line We know the field is a SalaryField class */
            FieldType::SALARY()->value => $this->serializeMoney($value, $field),
            /** @phpstan-ignore-next-line We know the field is a MultiField class */
            FieldType::MULTI()->value => $this->serializeMulti($value, $field, $withFieldInfo),
            default => throw new \RuntimeException('No definition for field type')
        };
    }

    protected function resolveSingleValue(mixed $value, string $type, int $optionMask): mixed
    {
        if (blank($value)) {
            return '';
        }

        $prefix = '';
        $suffix = '';

        if ($type === FieldType::SALARY()->value) {
            if (isset($value[self::FIELD_VALUE][$optionMask & self::IS_RANGE ? 2 : 1])) {
                $suffix .= ' '.trans('actions::description.item.salary.period.'.$value[self::FIELD_VALUE][$optionMask & self::IS_RANGE ? 2 : 1]);
            }
        }

        $label = $value[self::LABEL] ?? null;
        if ($label) {
            $prefix .= "[$label]: ";
        }
        if ($value[self::MAIN] ?? null) {
            $suffix .= ' (main)';
        }
        $value = $value[self::FIELD_VALUE] ?? null;

        if ($optionMask & self::IS_MULTI_SELECT) {
            return $prefix.implode(', ', array_filter(
                array_map(
                    fn ($val) => $this->resolveNestedValue($val, $type), $value ?: []
                )
            )).$suffix;
        }
        if ($optionMask & self::IS_RANGE) {
            $min = ($value[0] ?? null) ? $this->resolveNestedValue($value[0], $type) : null;
            $max = ($value[1] ?? null) ? $this->resolveNestedValue($value[1], $type) : null;
            $resolvedValue = match (true) {
                $min && $max => $prefix.$min.' - '.$max.$suffix,
                ! $min && $max => $prefix.'<'.$max.$suffix,
                $min && ! $max => $prefix.$min.'+'.$suffix,
                default => ''
            };
        } else {
            $resolvedValue = $prefix.$this->resolveNestedValue($type === FieldType::SALARY()->value ? ($value[0] ?? null) : $value, $type).$suffix;
        }

        return $resolvedValue;
    }

    protected function serializeMoney(array|string|int|float $value, MoneyField $field): string|array
    {
        if ($field->option('isRange')) {
            /** @var array $value */
            $currency = $field->getCurrency(fn () => $value[2]);
            $symbol = Currency::symbolMap()[$currency];

            $serializedValue = [$value[0] ? "$symbol$value[0]" : null, $value[1] ? "$symbol$value[1]" : null];
            if ($field->type()->is(FieldType::SALARY())) {
                /** @var \Mappings\Core\Mappings\Fields\Types\SalaryField $field */
                $serializedValue[] = $field->getPeriod(fn () => $value[3]);
            }

            return $serializedValue;
        }

        $amount = $value['amount'] ?? $value;

        /** @var array $value */
        $currency = $field->getCurrency(fn () => $value['currency']);
        /** @var string $value */
        $symbol = Currency::symbolMap()[$currency];

        if ($field->type()->is(FieldType::SALARY())) {
            /** @var \Mappings\Core\Mappings\Fields\Types\SalaryField $field */
            /** @var array $value */
            return ["$symbol$amount", $field->getPeriod(fn () => $value['period'])];
        }

        return "$symbol$amount";
    }

    protected function serializeMulti(array $value, MultiField $field, bool $withFieldInfo = true): array
    {
        return collect($value)->mapWithKeys(function ($val, $fieldId) use ($field, $withFieldInfo) {
            /** @var \Mappings\Core\Mappings\Fields\Field $subField */
            $subField = $field->fields()->find($fieldId);

            return [$fieldId => $this->formatFieldValueForAction($val, $subField, $withFieldInfo)];
        })->all();
    }
}
