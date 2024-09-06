<?php

declare(strict_types=1);

namespace App\Core\Imports\Importers;

use App\Models\Item;
use App\Models\Mapping;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Carbon;
use App\Core\Imports\ExcelUtils;
use App\Core\Imports\FieldsImporter;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use App\Core\Imports\RowImportFailedException;
use Mappings\Core\Mappings\Fields\Types\DateField;
use Mappings\Core\Mappings\Fields\Types\TimeField;

trait ImportsItems
{
    /**
     * @var \WeakMap<\App\Models\Item, array<int, array{
     *     row: int,
     *     fieldId: string,
     *     column: int,
     *     value: string|null,
     *     errors: string[],
     * }>>
     */
    protected \WeakMap $validationErrors;

    /**
     * @return array{
     *     row: int,
     *     fieldId: string,
     *     column: int,
     *     value: string|null,
     *     errors: string[],
     * }|null
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected function getValidationErrors(null|Carbon|string $value, Field $field, Row $row, int $column, ?string $dateFormat): ?array
    {
        $validator = FieldsImporter::getFieldValidator($value, $field, $dateFormat);
        if ($validator->fails()) {
            return [
                'row' => $row->getIndex(),
                'fieldId' => $field->id(),
                'column' => $column,
                'value' => $value instanceof Carbon ? $value->toDateString() : $value,
                'errors' => array_values($validator->errors()->all()),
            ];
        }

        return null;
    }

    protected function buildItem(Row $row, array $columnMap, Mapping $mapping, ?string $dateFormat = null): Item
    {
        $data = [];

        $allValidationErrors = [];

        foreach ($columnMap as $column => $fieldId) {
            $value = $row[$column] ?? null;
            /** @var \Mappings\Core\Mappings\Fields\Field $field */
            $field = $mapping->fields->getField($fieldId);

            if ($field->type()->is(FieldType::SYSTEM_NAME()) && (! $value || ! trim($value))) {
                throw new RowImportFailedException($row->getRowIndex(), 'Name field cannot be empty.');
            }

            // Excel stores dates as a number of days from 1900-01-01 so if the
            // field is a date field and the value is an integer, we can assume
            // it's an Excel date and convert it to a normal date.
            if (($field instanceof DateField || $field instanceof TimeField) && is_numeric($value)) {
                $value = ExcelUtils::convertDateCellToCarbon($value);
                if ($field instanceof TimeField) {
                    $value = $value->toTimeString();
                }
            }
            $validationErrors = $this->getValidationErrors($value, $field, $row, $column, $dateFormat);
            if ($validationErrors) {
                $allValidationErrors[] = $validationErrors;

                continue;
            }
            $serializedValue = FieldsImporter::importToField($value, $field, $dateFormat);
            if (! $serializedValue) {
                continue;
            }
            $fieldValue = [Field::VALUE => $serializedValue];

            $fieldIds = explode('.', $fieldId);
            $depth = count($fieldIds);
            $fieldData = &$data;
            foreach ($fieldIds as $key => $nestedFieldId) {
                if ($key + 1 === $depth) {
                    if ($field->isList()) {
                        if (! isset($fieldData[$nestedFieldId])) {
                            $fieldData[$nestedFieldId] = [Field::LIST_VALUE => []];
                        }
                        $fieldData[$nestedFieldId][Field::LIST_VALUE][] = $fieldValue;
                    } else {
                        $fieldData[$nestedFieldId] = $fieldValue;
                    }
                } else {
                    $fieldData[$nestedFieldId] = [Field::VALUE => []];
                    $fieldData = &$fieldData[$nestedFieldId][Field::VALUE];
                }
            }
        }

        $item = new Item(['data' => $data]);
        $item->mapping()->associate($mapping);

        if (! isset($this->validationErrors)) {
            $this->validationErrors = new \WeakMap;
        }
        $this->validationErrors[$item] = $allValidationErrors;

        return $item;
    }
}
