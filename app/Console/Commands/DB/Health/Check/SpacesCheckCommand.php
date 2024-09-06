<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Base;
use App\Core\BaseType;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class SpacesCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:spaces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all bases have at least one space';

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Base>
     */
    protected \Illuminate\Database\Eloquent\Collection $spacelessBases;

    protected function check(OutputInterface $output): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Base> $spacelessBases */
        $spacelessBases = Base::query()->doesntHave('spaces')
            ->get(['id', 'name', 'created_at', 'updated_at']);

        $this->spacelessBases = $spacelessBases;

        if ($this->spacelessBases->isNotEmpty()) {
            $message = $this->spacelessBases->count().' bases were found without a space.';
            $this->error($message);
            $this->table(['id', 'name', 'created_at', 'updated_at'], $this->spacelessBases);

            $this->report($message);
        }

        if ($this->spacelessBases->isEmpty()) {
            $this->info('All bases have spaces');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->spacelessBases->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->spacelessBases->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to create spaces for bases?')) {
                $this->spacelessBases
                    ->each(function (Base $base) {
                        $name = $base->type === BaseType::PERSONAL ? 'Personal' : 'Main';
                        $base->run(fn () => $base->spaces()->create(['name' => $name]));
                    });
            }
        } else {
            $this->info('No fixes required for the spaces table.');
        }

        return 0;
    }
}
