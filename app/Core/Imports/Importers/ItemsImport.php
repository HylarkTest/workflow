<?php

declare(strict_types=1);

namespace App\Core\Imports\Importers;

use App\Models\Item;
use App\Models\Import;
use App\Models\Mapping;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Arr;
use App\Core\Imports\ImportItemStatus;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Core\Imports\RowImportFailedException;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

/**
 * @phpstan-type ColumnMap = array{
 *     column: int,
 *     fieldId: string,
 * }[]
 */
class ItemsImport implements OnEachRow, ShouldQueue, WithChunkReading, WithEvents, WithStartRow
{
    use Importable;
    use ImportsItems;
    use RegistersEventListeners;
    use RemembersChunkOffset;
    use SerializesModels;

    /**
     * @var array<int, string>
     */
    protected array $columnMap;

    public string $queue = 'imports';

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item>
     */
    protected Collection $items;

    /**
     * @var array{ index: int }|null
     */
    protected ?array $endCursor = null;

    protected int $index = 0;

    /**
     * @param  ColumnMap  $columnMap
     */
    public function __construct(
        array $columnMap,
        protected Mapping $mapping,
        protected bool $firstRowIsHeader,
        protected ?string $dateFormat,
        protected Import $import
    ) {
        $this->columnMap = Arr::pluck($columnMap, 'fieldId', 'column');
        $this->items = $mapping->items()->getModel()->newCollection();
    }

    public function onRow(Row $row): void
    {
        // If the import was cancelled or reverted then we just skip through everything
        if (! $this->import->isImporting()) {
            return;
        }
        $this->index++;
        // When we start the import we count the total rows, and for that to be
        // quick it cannot check if the row is empty. So we cannot skip empty
        // rows here if we want the processed count to match the total count
        if ($row->isEmpty()) {
            return;
        }

        try {
            $item = $this->buildItem($row, $this->columnMap, $this->mapping, $this->dateFormat);

            $item->save();
            $item->imports()->attach($this->import, [
                'row' => $row->getRowIndex(),
                'status' => ImportItemStatus::IMPORTED,
            ]);
        } catch (RowImportFailedException $e) {
            $this->import->importables()->create([
                'row' => $row->getRowIndex(),
                'status' => ImportItemStatus::FAILED,
                'failure_reason' => $e->getMessage(),
                'importable_type' => (new Item)->getMorphClass(),
            ]);
        }
    }

    public function chunkSize(): int
    {
        return config('hylark.imports.chunk_size', 100);
    }

    public function beforeSheet(BeforeSheet $event): void
    {
        if (! $this->import->isImporting()) {
            return;
        }
        Item::disableSearchSyncing();
        Item::disableGlobalSearchSyncing();
    }

    public function afterChunk(AfterChunk $event): void
    {
        if (! $this->import->isImporting()) {
            return;
        }
        $originalScoutQueue = config('scout.queue');
        $originalFinderQueue = config('finder.queue');
        config([
            'scout.queue' => false,
            'finder.queue' => false,
        ]);
        $this->items->searchable();
        $this->items->globallySearchable();
        config([
            'scout.queue' => $originalScoutQueue,
            'finder.queue' => $originalFinderQueue,
        ]);
        Item::enableSearchSyncing();
        Item::enableGlobalSearchSyncing();
        $this->import->addProcessedItems($this->index);
    }

    public function importFailed(ImportFailed $event): void
    {
        $this->import->markAsFailed();
    }

    public function startRow(): int
    {
        return $this->firstRowIsHeader ? 2 : 1;
    }
}
