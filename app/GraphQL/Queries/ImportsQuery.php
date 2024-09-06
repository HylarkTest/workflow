<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Import;
use App\Models\Mapping;
use Lampager\Paginator;
use App\GraphQL\AppContext;
use App\Core\Imports\FieldsImporter;
use LighthouseHelpers\Core\Mutation;
use App\Core\Imports\SpreadsheetParser;
use App\Core\Imports\SpreadsheetImporter;
use App\Core\Imports\ImportFileRepository;
use Illuminate\Contracts\Validation\Factory;
use Rector\Exception\ShouldNotHappenException;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;

/**
 * @phpstan-type ColumnMapInput = array{
 *     column: int,
 *     fieldId: string,
 * }
 * @phpstan-type ParseSpreadsheetInput = array{
 *     input: array{
 *         file?: \Illuminate\Http\UploadedFile,
 *         fileId?: string|null,
 *         mappingId?: string|null,
 *     }
 * }
 * @phpstan-type ImportSpreadsheetInput = array{
 *     input: array{
 *         name: string,
 *         file?: \Illuminate\Http\UploadedFile,
 *         fileId?: string|null,
 *         mappingId: string|null,
 *         columnMap: ColumnMapInput[],
 *         firstRowIsHeader: bool,
 *         dateFormat?: string,
 *     }
 * }
 */
class ImportsQuery extends Mutation
{
    use PaginatesQueries;

    public function __construct(
        Factory $validationFactory,
        protected ImportFileRepository $importFileRepository,
        protected SpreadsheetParser $spreadsheetParser,
        protected SpreadsheetImporter $spreadsheetImporter
    ) {
        parent::__construct($validationFactory);
    }

    /**
     * @param array{
     *     file?: \Illuminate\Http\UploadedFile,
     *     fileId?: string|null,
     * } $input
     * @return array{ 0: \Illuminate\Http\UploadedFile|string, 1: string }
     */
    protected function getImportFile(array $input): array
    {
        if ($input['file'] ?? false) {
            $file = $input['file'];
            $fileId = $this->importFileRepository->storeTemporaryFile($file);
        } elseif ($input['fileId'] ?? false) {
            $fileId = $input['fileId'];
            $file = $this->importFileRepository->getFilePath($fileId);
        } else {
            throw new ShouldNotHappenException('Validation ensures that either file or fileId is present.');
        }

        return [$file, $fileId];
    }

    /**
     * @param  null  $root
     * @param  array{ first: int, after?: string }  $args
     *
     * @throws \JsonException
     */
    public function index($root, array $args, AppContext $context): SyncPromise
    {
        return $this->paginateQuery(
            $context->base()->imports(),
            $args,
            fn (Paginator $lampager) => $lampager->orderBy('created_at', 'desc'),
        );
    }

    /**
     * @param  null  $root
     */
    public function show($root, array $args, AppContext $context): Import
    {
        return $context->base()->imports()->findOrFail($args['id']);
    }

    /**
     * @param  null  $root
     * @param  ParseSpreadsheetInput  $args
     */
    public function parse($root, array $args, AppContext $context): array
    {
        $input = $args['input'];
        $mapping = null;
        if ($input['mappingId'] ?? null) {
            $mapping = $context->base()->mappings()->findOrFail($input['mappingId']);
        }
        [$file, $fileId] = $this->getImportFile($input);

        $parsed = $this->spreadsheetParser->parseFile($file, $mapping);
        $rows = $parsed['rows'];

        return $this->mutationResponse(200, 'Parsed successfully', ['data' => [
            'fileId' => $fileId,
            'headers' => $parsed['headers'],
            'rows' => fn () => $rows->map(function ($row, $index) {
                return ['row' => $index, 'data' => $row];
            }),
            'data' => fn () => collect($rows[0] ?? [])->map(function ($_, $index) use ($rows) {
                return ['column' => $index, 'data' => $rows->pluck($index)];
            }),
            'columnMapGuess' => $parsed['columnMapGuess'],
            'dateFormatGuess' => $parsed['dateFormatGuess'],
        ]]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    public function revert($root, array $args, AppContext $context): array
    {
        /** @var \App\Models\Import $import */
        $import = $context->base()->imports()->findOrFail($args['input']['id']);
        if (! $import->isCompleted()) {
            $this->throwValidationException('input.id', 'Cannot revert an import that is currently running.');
        }
        $import->revertImport();

        return $this->mutationResponse(200, 'Import revert started', ['import' => $import]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    public function cancel($root, array $args, AppContext $context): array
    {
        /** @var \App\Models\Import $import */
        $import = $context->base()->imports()->findOrFail($args['input']['id']);
        if (! $import->isImporting()) {
            $this->throwValidationException('input.id', 'Cannot cancel an import that is not currently running.');
        }
        $import->cancelImport();

        return $this->mutationResponse(200, 'Import cancel started', ['import' => $import]);
    }

    /**
     * @param  null  $root
     * @param  ImportSpreadsheetInput  $args
     */
    public function store($root, array $args, AppContext $context): array
    {
        $member = $context->baseUser();
        $input = $args['input'];
        /** @var \App\Models\Mapping $mapping */
        $mapping = $context->base()->mappings()->findOrFail($input['mappingId']);
        [$file, $fileId] = $this->getImportFile($input);

        $columnMap = $input['columnMap'];

        $this->validateColumnMap($columnMap, $mapping);

        $import = $this->spreadsheetImporter->import(
            $input['name'],
            $member,
            $file,
            $mapping,
            $columnMap,
            $input['firstRowIsHeader'],
            $input['dateFormat'] ?? null,
        );

        return $this->mutationResponse(200, 'Import started', ['import' => $import]);
    }

    public function prepareFileForPreview(Mapping $mapping, array $args, AppContext $context): array
    {
        $input = $args['input'];
        [$file, $fileId] = $this->getImportFile($input);

        return $this->mutationResponse(200, 'File prepared for preview', [
            $mapping->api_name => fn ($root, array $args) => $this->spreadsheetImporter->preview(
                $file,
                $mapping,
                $input['columnMap'],
                $input['firstRowIsHeader'],
                $input['dateFormat'] ?? null,
                $args['first'],
                $args['page'],
            ),
        ]);
    }

    /**
     * @param  ColumnMapInput[]  $columnMap
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function validateColumnMap(array $columnMap, Mapping $mapping): void
    {
        $validatedFieldIds = [];
        foreach ($columnMap as $index => $map) {
            $fieldId = $map['fieldId'];
            $field = $mapping->fields->getField($fieldId);
            if (! $field) {
                $this->throwValidationException("input.columnMap.$index.fieldId", "Field with ID $fieldId does not exist in the mapping.");
            }
            if (! FieldsImporter::canImportField($field)) {
                $this->throwValidationException("input.columnMap.$index.fieldId", "Field with ID $fieldId cannot be imported.");
            }
            if (! $field->isList() && in_array($fieldId, $validatedFieldIds, true)) {
                $this->throwValidationException("input.columnMap.$index.fieldId", "Field with ID $fieldId is already mapped.");
            }
            $validatedFieldIds[] = $fieldId;
        }
    }
}
