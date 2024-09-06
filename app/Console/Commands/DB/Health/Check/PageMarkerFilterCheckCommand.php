<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageMarkerFilterCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:page-marker-filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the pages have valid marker filters.';

    /**
     * @var Collection<int, array>
     */
    protected Collection $pagesWithInvalidMarkers;

    /**
     * @var Collection<int, array>
     */
    protected Collection $invalidMarkerIds;

    protected function check(OutputInterface $output): int
    {
        $this->pagesWithInvalidMarkers = collect();
        $this->invalidMarkerIds = collect();

        $bar = $this->output->createProgressBar(Page::query()->count());

        Page::query()->each(function (Page $page) use ($bar) {
            $markerFilters = $page->markerFilters;
            $markerIds = collect($markerFilters)->pluck('markerId');
            foreach ($markerIds as $markerId) {
                try {
                    find($markerId);
                } catch (ModelNotFoundException) {
                    $this->invalidMarkerIds->push($markerId);
                    $this->pagesWithInvalidMarkers->push([
                        'id' => $page->id,
                        'type' => $page->type->value,
                        'marker_global_id' => $markerId,
                        'marker_id' => $this->decodeGlobalId($markerId),
                        'created_at' => $page->created_at,
                        'updated_at' => $page->updated_at,
                    ]);
                }
            }
            $bar->advance();
        });

        $this->info("\n");

        if ($this->pagesWithInvalidMarkers->isNotEmpty()) {
            $this->error($this->pagesWithInvalidMarkers->count().' pages were found with invalid marker filters.');
            $this->table(
                ['id', 'type', 'marker_id', 'actual_marker_id', 'created_at', 'updated_at'],
                $this->pagesWithInvalidMarkers->toArray()
            );

            $this->report($this->pagesWithInvalidMarkers->count().' pages were found with invalid marker filters.');
        } else {
            $this->info('All pages have valid marker filters.');
        }

        return 0;

    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {

        if ($this->pagesWithInvalidMarkers->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to remove all invalid marker filters from pages?')) {
                return $this->reset();
            }
        } else {
            $this->info('No fixes required for the actions table flags.');
        }

        return 0;
    }

    protected function reset(): int
    {
        if ($this->pagesWithInvalidMarkers->isEmpty()) {
            $this->info('No fixes required for marker filters.');
        } else {
            $bar = $this->output->createProgressBar($this->pagesWithInvalidMarkers->count());

            $this->pagesWithInvalidMarkers->each(function ($page) use ($bar) {
                /** @var Page $page */
                $page = Page::query()->find($page['id']);

                $markerFilters = collect($page->markerFilters)->filter(
                    fn ($filter) => ! $this->invalidMarkerIds->contains($filter['markerId'])
                )->values();

                $page->markerFilters = $markerFilters->isEmpty() ? null : $markerFilters->toArray();

                $page->save();

                $bar->advance();
            });
        }

        return 0;
    }

    private function decodeGlobalId(string $globalId): string
    {
        return Container::getInstance()
            ->make(GlobalId::class)
            ->decodeId($globalId);
    }
}
