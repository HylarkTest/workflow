<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait DatabaseMigrations
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate:fresh', $this->migrateFreshUsing());

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');

            RefreshDatabaseState::$migrated = false;
        });
    }
}
