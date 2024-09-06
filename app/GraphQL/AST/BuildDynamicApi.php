<?php

declare(strict_types=1);

namespace App\GraphQL\AST;

use App\Models\Base;
use App\Models\Mapping;
use Stancl\Tenancy\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Config\Repository;
use LighthouseHelpers\Core\AddsTypes;
use GraphQL\Type\Definition\ObjectType;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\GraphQL\Utils\MappingTypeBuilder;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;

class BuildDynamicApi
{
    use AddsTypes;

    public function __construct(
        protected Repository $config,
        protected TypeRegistry $registry,
        protected Dispatcher $events,
        protected MappingTypeBuilder $mappingTypeBuilder,
        protected GlobalId $globalId,
        protected Tenancy $tenancy,
    ) {}

    public function getRegistry(): \LighthouseHelpers\Core\TypeRegistry
    {
        /** @phpstan-ignore-next-line */
        return $this->registry;
    }

    /**
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function build(Base $base): void
    {
        $mappings = $this->getMappingsFromTenant($base);

        Request::macro('getMappingContext', fn () => $mappings);

        $this->getRegistry()->overwriteDynamic(new ObjectType(['name' => 'ItemQuery', 'fields' => []]));
        $this->getRegistry()->overwriteDynamic(new ObjectType(['name' => 'GroupedItemQuery', 'fields' => []]));
        $this->getRegistry()->overwriteDynamic(new ObjectType(['name' => 'ItemMutation', 'fields' => []]));
        $this->getRegistry()->overwriteDynamic(new ObjectType(['name' => 'ItemSubscription', 'fields' => []]));

        $mappings->each(function (Mapping $mapping) {
            $this->mappingTypeBuilder->registerDynamicTypes($mapping);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mapping>
     *
     * @throws \Throwable
     */
    protected function getMappingsFromTenant(Base $tenant): Collection
    {
        return $tenant->mappings;
    }
}
