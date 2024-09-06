<?php

declare(strict_types=1);

namespace App\Core\Imports;

use App\Models\Import;
use App\Models\Mapping;
use App\Models\BaseUserPivot;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\ImportFinished;
use App\Core\Imports\Importers\ItemsImport;
use App\Core\Imports\Importers\ItemsPreviewImport;

/**
 * @phpstan-import-type ColumnMap from \App\Core\Imports\Importers\ItemsPreviewImport
 */
class SpreadsheetImporter
{
    /**
     * @param  ColumnMap  $columnMap
     */
    public function import(
        string $name,
        BaseUserPivot $member,
        UploadedFile|string $file,
        Mapping $mapping,
        array $columnMap,
        bool $firstRowIsHeader,
        ?string $dateFormat,
    ): Import {
        $import = Import::startImport($name, $member, $file, $firstRowIsHeader);
        Excel::queueImport(
            new ItemsImport($columnMap, $mapping, $firstRowIsHeader, $dateFormat, $import),
            $file,
            $this->getDisk()
        )->onQueue('imports')->chain([function () use ($import) {
            $import->finishImport();
            // $import->member->user->notify(new ImportFinished($import));
        }]);

        return $import;
    }

    /**
     * @param  ColumnMap  $columnMap
     * @return \Illuminate\Pagination\Paginator<\App\Models\Item>
     */
    public function preview(
        UploadedFile|string $file,
        Mapping $mapping,
        array $columnMap,
        bool $firstRowIsHeader,
        ?string $dateFormat = null,
        int $first = 25,
        int $page = 1,
    ): Paginator {
        $importer = new ItemsPreviewImport($columnMap, $mapping, $firstRowIsHeader, $dateFormat);
        $importer->setPage($first, $page);
        Excel::import($importer, $file, $this->getDisk());

        $paginator = new Paginator(
            $importer->previewItems(),
            $first,
            $page,
            ['errors' => $importer->getImportErrors()]
        );

        $paginator->hasMorePagesWhen($importer->hasNextPage());

        return $paginator;
    }

    protected function getDisk(): string
    {
        return resolve(ImportFileRepository::class)->getImportsDisk();
    }
}
