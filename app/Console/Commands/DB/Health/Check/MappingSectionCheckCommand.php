<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Mapping;
use Illuminate\Support\Collection;
use Mappings\Core\Mappings\Fields\Field;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class MappingSectionCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:mapping-section';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The sections referenced by mapping fields should
point to valid sections';

    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected Collection $invalidFields;

    protected function check(OutputInterface $output): int
    {
        $this->invalidFields = collect();

        $bar = $this->output->createProgressBar(Mapping::query()->count());

        Mapping::query()->select('id', 'sections', 'fields')->each(function (Mapping $mapping) use ($bar) {
            $sections = $mapping->sections;

            $mapping->fields->each(function (Field $field) use ($mapping, $sections) {
                if ($field->getSection() && ! $sections->contains('id', $field->getSection())) {
                    $this->invalidFields->push([$mapping->getKey(), $field->id(), $field->getSection()]);
                }
            });

            $bar->advance();
        });

        $this->info("\n");

        if ($this->invalidFields->isNotEmpty()) {
            $message = $this->invalidFields->count().' invalid field sections were found in some mappings.';

            $this->error($message);
            $this->table(['mapping_id', 'field_id', 'section_id'], $this->invalidFields);

            $this->report($message);
        } else {
            $this->info('The field sections are all correct!');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->invalidFields->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if (! $this->numberToFix()) {
            $this->info('No fixes required for the mapping sections.');
        }

        foreach ($this->invalidFields as [$mappingId, $fieldId, $sectionId]) {
            if ($confirmFixes && ! $this->confirm("Would you like to remove the section [[$sectionId]] from the field [[$fieldId]] on mapping [[$mappingId]]?")) {
                $this->error("Not removing the section [[$sectionId]].");
            } else {
                $this->warn("Removing the section [[$sectionId]].");
                /** @var \Mappings\Models\Mapping $mapping */
                $mapping = Mapping::query()->find($mappingId);
                $fields = $mapping->fields;
                /** @var \Mappings\Core\Mappings\Fields\Field $field */
                $field = $fields->find($fieldId);
                $field->section = null;
                $mapping->update(['fields' => $fields]);
            }
        }

        return 0;
    }
}
