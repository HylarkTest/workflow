<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\GraphQL\AppContext;
use LighthouseHelpers\Core\Mutation;
use App\Core\Mappings\Features\MappingFeatureType;

class MappingFeatureQuery extends Mutation
{
    /**
     * @param  null  $rootValue
     */
    public function store($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $type = MappingFeatureType::from($args['input']['val']);

        $mapping->enableFeature($type, $args['input']['options']);

        return $this->mutationResponse(200, 'The mapping feature was updated successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $featureType = $args['input']['val'];
        $mapping->disableFeature(MappingFeatureType::from($featureType));

        return $this->mutationResponse(200, 'The mapping feature was disabled successfully', [
            'mapping' => $mapping,
        ]);
    }
}
