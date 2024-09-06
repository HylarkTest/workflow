<?php

declare(strict_types=1);

namespace Finder;

use Finder\Engines\Engine;
use Finder\Engines\NullEngine;
use Illuminate\Support\Manager;
use Finder\Engines\ElasticEngine;

class EngineManager extends Manager
{
    public function engine(?string $name = null): Engine
    {
        return $this->driver($name);
    }

    public function createNullDriver(): NullEngine
    {
        return new NullEngine;
    }

    public function createElasticDriver(): ElasticEngine
    {
        return resolve(ElasticEngine::class);
    }

    /**
     * @return $this
     */
    public function forgetEngines(): static
    {
        $this->drivers = [];

        return $this;
    }

    public function getDefaultDriver(): string
    {
        if (null === ($driver = config('finder.driver'))) {
            return 'null';
        }

        return $driver;
    }
}
