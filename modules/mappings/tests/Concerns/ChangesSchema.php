<?php

declare(strict_types=1);

namespace Tests\Mappings\Concerns;

trait ChangesSchema
{
    protected function getEnvironmentSetup($app): void
    {
        $app->make('config')->set('lighthouse.schema_cache.enable', false);
    }
}
