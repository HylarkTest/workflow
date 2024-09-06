<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Page;
use Illuminate\Support\Arr;
use App\Core\Pages\PageType;
use Illuminate\Database\Eloquent\Collection;
use App\Console\Commands\DB\Health\DBHealthCommand;
use App\Console\Commands\DB\Health\ChecksEnumColumns;
use Symfony\Component\Console\Output\OutputInterface;

class PageTypeCheckCommand extends DBHealthCommand
{
    use ChecksEnumColumns;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:page-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the pages have valid types and point to
appropriate models';

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page>
     */
    protected Collection $badEnums;

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page>
     */
    protected Collection $badMappings;

    protected function check(OutputInterface $output): int
    {
        $this->badEnums = $this->getInvalidEnums(Page::class, 'type');

        $this->badMappings = Page::query()
            ->whereIn('type', [PageType::ENTITIES, PageType::ENTITY])
            ->doesntHave('mapping')
            ->get();

        if ($this->badEnums->isNotEmpty()) {
            $message = $this->badEnums->count().' pages were found with invalid types that do not match the enum.';
            $this->error($message);
            $this->table(
                ['id', 'type', 'created_at', 'updated_at'],
                $this->badEnums->map(fn (Page $page) => Arr::only($page->getAttributes(), ['id', 'type', 'created_at', 'updated_at']))
            );

            $this->report($message);
        }

        if ($this->badMappings->isNotEmpty()) {
            $message = $this->badMappings->count().' entity pages do not point to valid mappings.';
            $this->error($message);
            $this->table(
                ['id', 'type', 'mapping_id', 'created_at', 'updated_at'],
                $this->badMappings->map(fn (Page $page) => Arr::only($page->getAttributes(), ['id', 'type', 'mapping_id', 'created_at', 'updated_at']))
            );

            $this->report($message);
        }

        if ($this->badEnums->isEmpty() && $this->badMappings->isEmpty()) {
            $this->info('All pages have the correct enums and relationships.');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->badEnums->count() + $this->badMappings->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        $this->error('Invalid pages cannot be fixed automatically');

        return 1;
    }
}
