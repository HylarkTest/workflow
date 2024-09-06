<?php

declare(strict_types=1);

namespace Tests\Mappings\Concerns;

use Mappings\Models\Mapping;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Mappings\Utils\Models\MappingContainer;

trait CreatesMappings
{
    protected function createMapping($attributes = [], ?\Mappings\Core\Mappings\Contracts\MappingContainer $container = null): Mapping
    {
        $mapping = Mapping::factory()->create($attributes);
        if ($container) {
            $container->mappings()->save($mapping);
        }

        return $mapping;
    }

    protected function createMappingContainer(): MappingContainer
    {
        return MappingContainer::query()->forceCreate([]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('mapping_containers', fn (Blueprint $table) => $table->id());

        Schema::table('mappings', fn (Blueprint $table) => $table->unsignedBigInteger('mapping_container_id')->nullable());
    }
}
