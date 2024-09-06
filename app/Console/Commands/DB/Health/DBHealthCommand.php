<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DBHealthCommand extends Command
{
    use ConfirmableTrait;

    protected OutputInterface $baseOutput;

    public function __construct()
    {
        parent::__construct();

        $this->getDefinition()->addOption(new InputOption(
            '--fix', null, InputOption::VALUE_NONE, 'Automatically fix any errors found'
        ));

        $this->getDefinition()->addOption(new InputOption(
            '--force', null, InputOption::VALUE_NONE, 'Don\'t ask to fix each error found'
        ));

        if ($this instanceof ResettableCommand) {
            $this->getDefinition()->addOption(new InputOption(
                '--reset', null, InputOption::VALUE_NONE, 'Re-populate the data from scratch'
            ));
        }

        $this->getDefinition()->addOption(new InputOption(
            '--report', null, InputOption::VALUE_NONE, 'Report the results to support team'
        ));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->baseOutput = $output;

        return parent::run($input, $output);
    }

    public function handle(): int
    {
        /** @phpstan-ignore-next-line Option is not set in signature */
        if (($this instanceof ResettableCommand) && $this->option('reset')) {
            if ($this->confirmToProceed()) {
                return $this->reset();
            }

            return 5;
        }

        $this->info('Running '.$this->commandName()."\n");

        $result = $this->check($this->baseOutput);

        if ($result) {
            return $result;
        }

        if ($this->option('fix')) {
            if ($count = $this->numberToFix()) {
                $this->line("Fixing $count errors");
            }
            if ($this->confirmToProceed()) {
                $this->info('Fixing '.$this->commandName()."\n");
                $result = $this->fix(! $this->option('force'), $this->baseOutput);

                return $result;
            }

            return 5;
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return 0;
    }

    abstract protected function check(OutputInterface $output): int;

    abstract protected function fix(bool $confirmFixes, OutputInterface $output): int;

    protected function commandName(): string
    {
        return str_replace(' Command', '', Str::title(Str::snake(class_basename($this), ' ')));
    }

    protected function report(string $message): int
    {
        if ($this->option('report')) {
            Log::channel('check')
                ->alert($message, ['command' => $this->commandName()]);
        }

        return 0;
    }
}
