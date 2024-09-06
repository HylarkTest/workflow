<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\Models\Mapping;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Mappings\Relationships\Relationship;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\GraphQL\Queries\Concerns\BroadcastsMappingChanges;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class MappingRelationshipQuery extends Mutation
{
    use BroadcastsMappingChanges;

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

        $this->validate(
            $args,
            [
                'input.name' => 'filled|max:'.Relationship::MAX_LENGTH,
                'input.apiName' => 'api_name|max:'.Relationship::MAX_LENGTH,
                'input.type' => 'required',
                'input.to' => 'required',
                'input.inverseName' => 'max:'.Relationship::MAX_LENGTH,
                'input.inverseApiName' => 'api_name|max:'.Relationship::MAX_LENGTH,
            ],
            $resolveInfo,
            Arr::dot($this->translator->get('validation.custom.relationship')),
            Arr::dot($this->translator->get('mappings::validation.attributes'))
        );

        $relationshipOptions = $args['input'];

        $type = RelationshipType::from($relationshipOptions['type']);

        $to = $relationshipOptions['to'];
        if (! ($to instanceof Mapping)) {
            /** @var \App\Models\Mapping $to */
            $to = Utils::resolveModelFromGlobalId($to);
        }

        $relationship = $mapping->addRelationship([
            'type' => $type,
            'to' => $to,
            'name' => $relationshipOptions['name'] ?? null,
            'apiName' => $relationshipOptions['apiName'] ?? null,
            'inverse' => false,
        ]);

        if ($relationship) {
            $id = $relationship->id();
            if ($to->is($mapping)) {
                $to = $mapping;
                $id = $relationship->getInverseId();
            }
            $to->addRelationship([
                'id' => $id,
                'type' => $type->inverse(),
                'to' => $mapping,
                'name' => $relationshipOptions['inverseName'] ?? null,
                'apiName' => $relationshipOptions['inverseApiName'] ?? null,
                'inverse' => true,
            ]);
            $this->broadcastMappingUpdated($to, 'Relationship was added successfully');
        }
        $mappings = request()->getMappingContext();
        /** @phpstan-ignore-next-line It must exist */
        $mappings->find($to->id)->refresh();

        return $this->mappingMutationResponse($mapping, 'Relationship was added successfully');
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

        $id = $args['input']['id'];

        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $mapping->removeRelationship($id);

        if ($relationship->to) {
            /*
             * If there is no inverse relationship we can ignore any exceptions
             * thrown.
             */
            try {
                /** @var \App\Models\Mapping $toMapping */
                $toMapping = $relationship->toMapping();
                if ($toMapping->is($mapping)) {
                    $toMapping = $mapping;
                    $id = $relationship->getInverseId();
                }
                $toMapping->removeRelationship($id);
                $this->broadcastMappingUpdated($toMapping, 'Relationship was deleted successfully');
            } catch (ModelNotFoundException $e) {
            }
        }

        return $this->mappingMutationResponse($mapping, 'Relationship was deleted successfully');
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $data = $this->validate(
            $args,
            [
                'input.name' => 'max:'.Relationship::MAX_LENGTH,
                'input.apiName' => 'api_name|max:'.Relationship::MAX_LENGTH,
                'input.inverseName' => 'max:'.Relationship::MAX_LENGTH,
                'input.inverseApiName' => 'api_name|max:'.Relationship::MAX_LENGTH,
            ],
            $resolveInfo,
            [],
            Arr::dot($this->translator->get('mappings::validation.attributes'))
        );

        $id = $args['input']['id'];

        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $mapping->updateRelationships($id, $data['input']);

        if (isset($args['input']['inverseName']) || isset($args['input']['inverseApiName'])) {
            /** @var \App\Models\Mapping $to */
            $to = $relationship->toMapping();
            $to->updateRelationships($id, [
                'name' => $args['input']['inverseName'] ?? null,
                'apiName' => $args['input']['inverseApiName'] ?? null,
            ]);
            $this->broadcastMappingUpdated($to, 'Relationship was updated successfully');
        }

        return $this->mappingMutationResponse($mapping, 'Relationship was updated successfully');
    }
}
