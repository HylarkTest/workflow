<?php

declare(strict_types=1);

namespace CitusLaravel\Commands;

use CitusLaravel\CitusHelpers;
use Illuminate\Console\Command;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;

class CitusDistributeCommand extends Command
{
    use CitusHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citus:distribute {--revert : Un-distribute all citus distributed tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cycle through all the tenant tables, and
    distribute them with Citus.
';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->citusInstalled()) {
            $this->error('You must have Citus installed to run this command');

            return 1;
        }

        $beforeDistributeCb = config('citus.before_distribute');
        if ($beforeDistributeCb) {
            $beforeDistributeCb();
        }

        try {
            if ($this->option('revert')) {
                $result = $this->down();
            } else {
                $result = $this->up();
            }
        } finally {
            $afterDistributeCb = config('citus.after_distribute');
            if ($afterDistributeCb) {
                $afterDistributeCb();
            }
        }

        return $result;
    }

    public function up(): int
    {
        /** @var \Doctrine\DBAL\Schema\PostgreSQLSchemaManager $manager */
        $manager = DB::connection()->getDoctrineSchemaManager();
        $prefix = DB::connection()->getTablePrefix();

        $distributedTables = DB::connection()
            ->setTablePrefix('')
            ->table('public.citus_tables')
            ->pluck('table_name');

        DB::connection()->setTablePrefix($prefix);

        $distributionTable = config('citus.distribution_table');
        $distributionColumn = config('citus.tenant_column');

        if ($distributedTables->doesntContain($prefix.$distributionTable)) {
            $this->info("Distributing table `$distributionTable`.");
            $this->createDistributedTable($distributionTable, 'id');
        } else {
            $this->warn("Skipping table `$distributionTable`, it has already been distributed.");
        }

        foreach (config('citus.reference_tables') as $referenceTable) {
            if ($distributedTables->doesntContain($prefix.$referenceTable)) {
                $this->info("Creating reference table `$referenceTable`.");
                $this->createReferenceTable($referenceTable);
            } else {
                $this->warn("Skipping table `$referenceTable`, it has already been distributed.");
            }
        }

        $tablesToDistribute = config('citus.distributed_tables');

        foreach ($tablesToDistribute as $table => $colocate) {
            if (\is_int($table)) {
                $table = $colocate;
                $colocate = 'default';
            }
            if ($distributedTables->contains($prefix.$table)) {
                $this->warn("Skipping table `$table`, it has already been distributed.");

                continue;
            }
            /** @var \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Column> $columns */
            $columns = collect($manager->listTableColumns($prefix.$table));
            if (! $columns->first(fn (Column $column) => $column->getName() === $distributionColumn)) {
                $this->error("Skipping table `$table`, it does not have a distribution column.");

                continue;
            }

            $this->info("Distributing table `$table`.");
            $this->createDistributedTable($table, $distributionColumn, $colocate);
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($distributionColumn, $distributionTable) {
                    $blueprint->foreign($distributionColumn)->references('id')->on($distributionTable)->cascadeOnDelete();
                });
            } catch (QueryException) {
                // Foreign key already exists
            }
        }

        return 0;
    }

    public function down(): int
    {
        DB::connection()->setTablePrefix('');
        $distributedTables = DB::connection()
            ->table('public.citus_tables')
            ->pluck('table_name');

        foreach ($distributedTables as $table) {
            $this->info("Un-distributing `$table`");
            if (DB::connection()->table('public.citus_tables')->whereRaw("table_name = '$table'::regclass")->doesntExist()) {
                $this->warn('Table has already been un-distributed via foreign key');
            } else {
                $this->undistributeTable($table);
            }
        }

        return 0;
    }
}
