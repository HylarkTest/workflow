<?php

declare(strict_types=1);

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use Elastic\Migrations\Migrator;
use Illuminate\Console\ConfirmableTrait;
use Elastic\Migrations\IndexManagerInterface;
use Elastic\Migrations\Repositories\MigrationRepository;

class HylarkFreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:fresh
        {--force : Force the operation to run when in production.}';

    /**
     * @var string
     */
    protected $description = 'Drop all indices and re-run all migrations.';

    public function handle(
        Migrator $migrator,
        MigrationRepository $migrationRepository,
        IndexManagerInterface $indexManager
    ): int {
        $migrator->setOutput($this->output);

        if (! $this->confirmToProceed() || ! $migrator->isReady()) {
            return 1;
        }

        $indexManager->dropIfExists('finder');
        $indexManager->dropIfExists('items');
        $migrationRepository->purge();
        $migrator->migrateAll();

        return 0;
    }
}
