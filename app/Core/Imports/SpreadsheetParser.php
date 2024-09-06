<?php

declare(strict_types=1);

namespace App\Core\Imports;

use App\Models\Mapping;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Mappings\Core\Mappings\Fields\Field;
use App\Core\Imports\Importers\GenericImport;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Mappings\Core\Mappings\Fields\Types\MultiField;

class SpreadsheetParser
{
    protected function fieldCanBeUsedInImport(Field $field): bool
    {
        return FieldsImporter::canImportField($field);
    }

    protected function matchingFieldId(string $header, FieldCollection $fields, string $prefix = ''): ?string
    {
        [$regularFields, $multiFields] = $fields->partition(fn ($field) => ! $field instanceof MultiField);
        foreach ($regularFields as $field) {
            if ($this->fieldCanBeUsedInImport($field) && strcasecmp($field->name, $header) === 0) {
                return "$prefix$field->id";
            }
        }
        foreach ($multiFields as $field) {
            $matchingFieldId = $this->matchingFieldId($header, $field->fields(), "$prefix$field->id.");
            if ($matchingFieldId) {
                return $matchingFieldId;
            }
        }

        return null;
    }

    /**
     * @return array{
     *     headers: string[],
     *     rows: \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, string>>,
     *     columnMapGuess: array{column: int, fieldId: string}[],
     *     dateFormatGuess: string|null
     * }
     */
    public function parseFile(UploadedFile|string $file, ?Mapping $mapping = null): array
    {
        $importer = new GenericImport;

        $rows = $importer->toCollection($file, resolve(ImportFileRepository::class)->getImportsDisk())->first();

        $headers = $rows->get(0);

        if ($headers->isNotEmpty()) {
            $headers->each(function ($header, $index) use ($rows) {
                if ($rows->every(fn ($row) => ! $row->get($index))) {
                    $rows->each(fn ($row) => $row->forget($index));
                }
            });
        }

        $columnMapGuess = [];
        $matchedFields = [];

        if ($mapping) {
            foreach ($headers as $index => $header) {
                if (! $header) {
                    continue;
                }
                $matchingFieldId = $this->matchingFieldId($header, $mapping->fields);
                if ($matchingFieldId) {
                    /** @var \Mappings\Core\Mappings\Fields\Field $field */
                    $field = $mapping->fields->getField($matchingFieldId);
                    if ($field->isList() || ! in_array($matchingFieldId, $matchedFields, true)) {
                        $columnMapGuess[] = ['column' => $index, 'fieldId' => $matchingFieldId];
                        $matchedFields[] = $matchingFieldId;
                    }
                }
            }
        }

        $dateFormatGuesses = null;

        foreach (Arr::collapse($rows) as $cellValue) {
            $possibleDateFormats = FieldsImporter::possibleDateFormats($cellValue);
            if ($possibleDateFormats) {
                if (! $dateFormatGuesses) {
                    $dateFormatGuesses = $possibleDateFormats;
                } else {
                    $dateFormatGuesses = array_intersect($dateFormatGuesses, $possibleDateFormats);
                }
            }
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'columnMapGuess' => $columnMapGuess,
            'dateFormatGuess' => $dateFormatGuesses[0] ?? null,
        ];
    }
}
