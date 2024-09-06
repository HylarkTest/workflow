<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\Models\Marker;
use App\Models\Mapping;
use App\GraphQL\AppContext;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Translation\Translator;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MappingMarkerGroupQuery extends Mutation
{
    protected Translator $translator;

    public function __construct(Factory $validationFactory, Translator $translator)
    {
        parent::__construct($validationFactory);
        $this->translator = $translator;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $data = $this->validate($args, [
            'input.name' => ['required', 'max:'.MappingMarkerGroup::MAX_LENGTH],
            'input.apiName' => ['string', 'max:'.MappingMarkerGroup::MAX_LENGTH, 'api_name', Rule::notIn($mapping->markerGroups?->pluck('apiName') ?: [])],
            'input.group' => ['required'],
            'input.relationship' => ['nullable', 'string'],
        ], $resolveInfo, [], Arr::dot($this->translator->get('validation.attributes')));

        $markerGroup = $data['input'];
        // Validate if the marker group exists
        /** @var \App\Models\MarkerGroup $group */
        $group = MarkerGroup::query()->findOrFail($markerGroup['group']);
        $markerGroup['group'] = $group;
        $markerGroup['type'] = $group->type;

        if ($relationshipId = $markerGroup['relationship'] ?? null) {
            /* @var \Mappings\Core\Mappings\Relationships\Relationship|null $relationship */
            $markerGroup['relationship'] = $mapping->relationships->find($relationshipId);
            if ($markerGroup['relationship'] === null) {
                throw (new ModelNotFoundException("Unable to find relationship with id: $relationshipId"))->setModel(Mapping::class, $mapping->getKey());
            }
        }

        $mapping->addMarkerGroup($markerGroup);

        if ($markerGroup['relationship'] ?? null) {
            /** @var \App\Models\Mapping $toMapping */
            $toMapping = $markerGroup['relationship']->toMapping();

            try {
                $toMapping->addMarkerGroup($markerGroup);
            } catch (ModelNotFoundException $e) {
            }
        }

        return $this->mutationResponse(200, 'Marker group was added successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);
        $id = $args['input']['id'];

        $data = $this->validate($args, [
            'input.name' => ['max:'.MappingMarkerGroup::MAX_LENGTH],
            'input.apiName' => ['max:'.MappingMarkerGroup::MAX_LENGTH, 'api_name', Rule::notIn($mapping->markerGroups?->pluck('apiName') ?: [])],
        ], $resolveInfo, [], Arr::dot($this->translator->get('validation.attributes')));

        $mapping->updateMarkerGroup($id, $data['input']);

        return $this->mutationResponse(200, 'The marker group has been updated', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);
        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup|null $mappingMarkerGroup */
        $mappingMarkerGroup = $mapping->markerGroups?->find($args['input']['id']);
        if (! $mappingMarkerGroup) {
            throw (new ModelNotFoundException("Unable to find marker group with id: {$args['input']['id']}"))->setModel(Mapping::class, $mapping->getKey());
        }

        $mapping->validateRemovingMarkerGroup($mappingMarkerGroup, 'input.usedByMappings');

        $mapping->removeMarkerGroup($args['input']['id']);

        if ($mappingMarkerGroup->relationship) {
            /** @var \App\Models\Mapping $toMapping */
            $toMapping = $mappingMarkerGroup->relationship->toMapping();
            try {
                $toMapping->removeMarkerGroup($args['input']['id']);
            } catch (ModelNotFoundException) {
            }
        }

        return $this->mutationResponse(200, 'The marker group has been deleted', [
            'mapping' => $mapping,
        ]);
    }
}
