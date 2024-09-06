<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Mapping;
use Illuminate\Support\Collection;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Mappings\Core\Mappings\Relationships\Relationship;

class RelationshipMappingCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:relationship-mapping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relationships should point to existing mappings.';

    /**
     * @var \Illuminate\Support\Collection<int, array<int, mixed>>
     */
    protected Collection $invalidRelationships;

    protected function check(OutputInterface $output): int
    {
        $this->invalidRelationships = Collection::make();

        $bar = $this->output->createProgressBar(Mapping::query()->count());

        Mapping::query()->each(function (Mapping $mapping) use ($bar) {
            $mapping->relationships->each(function (Relationship $relationship) use ($mapping) {
                if (Mapping::query()->find($relationship->toId()) === null) {
                    $this->invalidRelationships[] = [$mapping->getKey(), $relationship->id(), $relationship->toId()];
                }
            });
            $bar->advance();
        });

        $this->info("\n");

        if ($this->invalidRelationships->isNotEmpty()) {
            $message = 'Found '.$this->numberToFix().' invalid relationships defined on some mappings.';
            $this->error($message);
            $this->table(['mapping_id', 'relationship_id', 'to_mapping_id'], $this->invalidRelationships);

            $this->report($message);
        } else {
            $this->info('The relationships are all correct!');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->invalidRelationships->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if (! $this->numberToFix()) {
            $this->info('No fixes required for the relationships.');
        }

        foreach ($this->invalidRelationships as [$mappingId, $relationshipId]) {
            if ($confirmFixes && ! $this->confirm("Would you like to remove the relationship [[$relationshipId]] for the mapping [[$mappingId]]?")) {
                $this->error("Not removing the relationship [[$relationshipId]].");
            } else {
                $this->warn("Removing the relationship [[$relationshipId]].");
                /** @var \App\Models\Mapping $mapping */
                $mapping = Mapping::query()->find($mappingId);
                $mapping->removeRelationship($relationshipId);
            }
        }

        return 0;
    }
}
