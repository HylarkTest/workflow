<?php

declare(strict_types=1);

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use App\Search\MappingIndexConfigurator;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Elasticsearch and migrate data.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //        $this->call('elastic:create-index', ['index-configurator' => MappingIndexConfigurator::class]);

        return 0;
    }
}
