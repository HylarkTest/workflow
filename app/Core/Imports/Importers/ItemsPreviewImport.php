<?php

declare(strict_types=1);

namespace App\Core\Imports\Importers;

use App\Models\Item;
use App\Models\Mapping;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Core\Imports\RowImportFailedException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

/**
 * @phpstan-type ColumnMap = array{
 *     column: int,
 *     fieldId: string,
 * }[]
 */
class ItemsPreviewImport implements OnEachRow, SkipsEmptyRows, WithChunkReading, WithEvents, WithLimit, WithStartRow
{
    use Importable;
    use ImportsItems;
    use RegistersEventListeners;
    use RemembersChunkOffset;

    /**
     * @var array<int, string>
     */
    protected array $columnMap;

    /**
     * @phpstan-ignore-next-line
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, ?\App\Models\Item>
     */
    protected Collection $items;

    protected int $first = 25;

    protected int $offset = 0;

    protected bool $hasNextPage = false;

    protected bool $hasPreviousPage = false;

    /**
     * @var array{ index: int }|null
     */
    protected ?array $endCursor = null;

    protected int $index = 0;

    protected array $importErrors = [];

    /**
     * @param  ColumnMap  $columnMap
     */
    public function __construct(
        array $columnMap,
        protected Mapping $mapping,
        protected bool $firstRowIsHeader,
        protected ?string $dateFormat,
    ) {
        $this->columnMap = Arr::pluck($columnMap, 'fieldId', 'column');
        $this->items = $mapping->items()->getModel()->newCollection();
    }

    public function onRow(Row $row): void
    {
        $this->index++;
        if ($this->index - 1 < $this->offset) {
            $this->hasPreviousPage = true;

            return;
        }
        if ($this->items->count() >= $this->first) {
            $this->hasNextPage = true;

            return;
        }

        try {
            $item = $this->buildItem($row, $this->columnMap, $this->mapping, $this->dateFormat);

            $item->setIsPreview(true);
            $item->updateTimestamps();
            $this->items->push($item);
        } catch (RowImportFailedException $exception) {
            $this->items->push(null);
            $this->importErrors[] = [
                'row' => $exception->row,
                'error' => $exception->reason,
                'path' => [$this->items->count() - 1],
            ];
        }
    }

    public function getImportErrors(): array
    {
        return $this->importErrors;
    }

    public function setPage(int $first, int $page = 1): void
    {
        $this->first = $first;
        $this->offset = ($page - 1) * $first;
    }

    public function startRow(): int
    {
        return $this->firstRowIsHeader ? 2 : 1;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ?\App\Models\Item>
     */
    public function previewItems(): Collection
    {
        return $this->items->each(function (?Item $item) {
            $item?->setAttribute('errors', $this->validationErrors[$item] ?? []);
        });
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    public function endCursor(): ?array
    {
        return $this->endCursor;
    }

    public function limit(): int
    {
        return $this->offset + $this->first + 1;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
