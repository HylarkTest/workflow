<?php

declare(strict_types=1);

namespace KeyValueStore\Commands;

use Illuminate\Console\Command;
use KeyValueStore\KeyValueStore;

class StoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key-value:store
                            {action : The action you want to take on the store}
                            {--scope= : The user ID to clear the store for}
                            {--key= : Clear the store for the given key}
                            {--force : Clear the store without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the key-value store';

    /**
     * Execute the console command.
     */
    public function handle(KeyValueStore $store): int
    {
        $scope = $this->option('scope');

        $key = $this->option('key');

        $action = $this->argument('action');

        if (! \in_array($action, ['clear', 'get', 'keys'], true)) {
            $this->error('Sorry, that is an invalid action');

            return 1;
        }

        return $this->$action($store, $scope, $key);
    }

    protected function clear(KeyValueStore $store, ?string $scope, ?string $key): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('This will clear the key-value store. Are you sure?')) {
                return 1;
            }
        }

        if ($scope) {
            if ($key) {
                $store->deleteValue($key, $scope);
            } else {
                $store->deleteScope($scope);
            }
        } else {
            if ($key) {
                $store->deleteAllKeys($key);
            } else {
                $store->deleteAll();
            }
        }

        return 0;
    }

    protected function keys(KeyValueStore $store, ?string $scope): int
    {
        if (! $scope) {
            $this->error('You must specify the scope to see their keys');

            return 1;
        }
        $keys = $store->getKeysForScope($scope);

        $this->table(['index', 'key'], $keys->map(fn (string $key, int $index) => [$index, $key]));

        if ($keys->isEmpty()) {
            return 0;
        }

        $index = $this->ask('Input an index to see the value of the key', null);

        if ($index !== null) {
            $key = $keys->get($index);
            if (! $key) {
                $this->error('There is no key with that index');

                return 1;
            }

            return $this->get($store, $scope, explode(':', $key)[2]);
        }

        return 0;
    }

    protected function get(KeyValueStore $store, ?string $scope, ?string $key): int
    {
        if (! $scope || ! $key) {
            $this->error('You must specify the scope and the key to fetch a value from the store');

            return 1;
        }

        $value = $store->getValue($key, $scope);

        $this->info($value);

        return 0;
    }
}
