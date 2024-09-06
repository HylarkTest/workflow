<?php

declare(strict_types=1);

use App\Models\Mapping;
use App\Models\MarkerGroup;
use Markers\Core\MarkerType;
use App\Core\MappingActionType;
use Illuminate\Database\Eloquent\Model;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Mappings\Core\Mappings\Relationships\Relationship;
use Mappings\Core\Mappings\Relationships\RelationshipType;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

test('the markers feature can be enabled on a mapping', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers']);

    $this->be($user);
    postCreateMappingMarkerGroup($mapping, $markerGroup);

    $mapping = $mapping->fresh();
    expect($mapping->markerGroups)->toHaveCount(1);
});

test('a marker group added on a relationship is copied on the related mapping', function () {
    $user = createUser();
    $fromMapping = createMapping($user, ['features' => []]);
    $toMapping = createMapping($user, ['features' => []]);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers']);

    $fromRelationship = addRelationship($fromMapping, $toMapping, RelationshipType::ONE_TO_MANY, true);

    $this->be($user);
    postCreateMappingMarkerGroup($fromMapping, $markerGroup, $fromRelationship);

    /** @var \App\Models\Mapping $toMapping */
    $toMapping = $toMapping->fresh();
    expect($toMapping->markerGroups)->toHaveCount(1);
    $mappingMarkerGroup = $toMapping->markerGroups->first();
    expect($mappingMarkerGroup->name)->toBe('Markers')
        ->and($mappingMarkerGroup->apiName)->toBe('markers')
        ->and($mappingMarkerGroup->group)->toBe($markerGroup->getKey())
        ->and(MarkerType::TAG)->toBe($mappingMarkerGroup->type)
        ->and($mappingMarkerGroup->relationship->id)->toBe($fromRelationship->id);
});

test('a marker name and type can be changed', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            updateMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\",
                name: \"Better markers\",
                apiName: \"betterMarkers\", type: STATUS
            }) {
                code
                mapping { id }
            }
        }
    ")->assertSuccessfulGraphQL();

    $mapping = $mapping->fresh();
    expect($mapping->markerGroups)->toHaveCount(1);
    $mappingMarkerGroup = $mapping->markerGroups->first();
    expect($mappingMarkerGroup->name)->toBe('Better markers')
        ->and($mappingMarkerGroup->apiName)->toBe('betterMarkers')
        ->and(MarkerType::TAG)->toBe($mappingMarkerGroup->type);
});

test('relationship markers are deleted when the relationship is deleted from the mapping', function () {
    $user = createUser();
    $fromMapping = createMapping($user, []);
    $toMapping = createMapping($user, []);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);

    $fromRelationship = addRelationship($fromMapping, $toMapping, RelationshipType::ONE_TO_MANY, true);
    addGroup($fromMapping, $markerGroup, ['relationship' => $fromRelationship]);

    expect($toMapping->fresh()->markerGroups)->toHaveCount(1);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingRelationship(input: {
                mappingId: \"$fromMapping->global_id\",
                id: \"$fromRelationship->id\"
            }) { code }
        }
    ")->assertSuccessfulGraphQL();

    $toMapping = $toMapping->fresh();
    $fromMapping = $fromMapping->fresh();

    expect($toMapping->relationships)->toBeEmpty();
    expect($fromMapping->relationships)->toBeEmpty();
    expect($toMapping->markerGroups)->toBeEmpty();
    expect($fromMapping->markerGroups)->toBeEmpty();
});

test('marker options can be deleted', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertSuccessfulGraphQL();

    $mapping = $mapping->fresh();
    expect($mapping->markerGroups)->toBeEmpty();
});

test('deleting a marker group removes it from the mapping settings', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            deleteMarkerGroup(input: {
                id: \"$markerGroup->global_id\"
            }) {
                code
            }
        }
    ")->assertSuccessfulGraphQL();

    $mapping = $mapping->fresh();
    expect($mapping->markerGroups)->toBeEmpty();
});

test('updating a marker name updates the mapping config if it is the same', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    addGroup($mapping, $markerGroup);
    addGroup($mapping, $markerGroup, ['name' => 'Custom name']);

    $this->be($user)->graphQL("
        mutation {
            updateMarkerGroup(input: {
                id: \"$markerGroup->global_id\"
                name: \"tags\"
            }) {
                code
            }
        }
    ")->assertSuccessfulGraphQL();

    $mapping->refresh();
    expect($mapping->markerGroups->first()->name)->toBe('tags')
        ->and($mapping->markerGroups->last()->name)->toBe('Custom name');
});

test('relationship marker options are deleted on both mappings', function () {
    $user = createUser();
    $fromMapping = createMapping($user, ['features' => []]);
    $toMapping = createMapping($user, ['features' => []]);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);

    $fromRelationship = addRelationship($fromMapping, $toMapping, RelationshipType::ONE_TO_MANY, true);
    $options = addGroup($fromMapping, $markerGroup, ['relationship' => $fromRelationship]);

    expect($toMapping->fresh()->markerGroups)->toHaveCount(1);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingMarkerGroup(input: {
                mappingId: \"$fromMapping->global_id\"
                id: \"$options->id\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertSuccessfulGraphQL();

    expect($fromMapping->fresh()->markerGroups)->toBeEmpty()
        ->and($toMapping->fresh()->markerGroups)->toBeEmpty();
});

test('marker options cannot have the same api name', function () {
    $user = createUser();
    $fromMapping = createMapping($user, ['features' => []]);
    $toMapping = createMapping($user, ['features' => []]);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    addGroup($fromMapping, $markerGroup);
    $fromRelationship = addRelationship($fromMapping, $toMapping);

    $this->be($user)->graphQL("
        mutation {
            createMappingMarkerGroup(input: {
                mappingId: \"$fromMapping->global_id\",
                name: \"Markers\",
                group: \"$markerGroup->global_id\",
                relationship: \"$fromRelationship->id\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertSuccessfulGraphQL();

    $fromMapping = $fromMapping->fresh();
    $toMapping = $toMapping->fresh();
    expect($fromMapping->markerGroups->first()->apiName)->toBe('markers')
        ->and($fromMapping->markerGroups->last()->apiName)->toBe('markers2')
        ->and($toMapping->markerGroups->first()->apiName)->toBe('markers');
});

test('marker names cannot be too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);

    $name = str_repeat('a', MappingMarkerGroup::MAX_LENGTH + 1);

    $this->be($user)->graphQL("
        mutation {
            createMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                name: \"$name\",
                apiName: \"1$name\",
                group: \"$markerGroup->global_id\",
            }) {
                code
                mapping { id }
            }
        }
    ")->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.name' => ['The name must not be greater than 50 characters.'],
        'input.apiName' => [
            'The API name must not be greater than 50 characters.',
            'The API name must start with a letter and contain only letters, numbers, and "_" (with no spaces).',
        ],
    ]]]]]);
});

test('marker names cannot be changed to be too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);

    $name = str_repeat('a', MappingMarkerGroup::MAX_LENGTH + 1);

    $this->be($user)->graphQL("
        mutation {
            updateMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\",
                name: \"$name\",
                apiName: \"1$name\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.name' => ['The name must not be greater than 50 characters.'],
        'input.apiName' => [
            'The API name must not be greater than 50 characters.',
            'The API name must start with a letter and contain only letters, numbers, and "_" (with no spaces).',
        ],
    ]]]]]);
});

test('a marker api name cannot be changed to an already existing one', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);
    addGroup($mapping, $markerGroup, ['name' => 'Other markers']);

    $this->be($user)->graphQL("
        mutation {
            updateMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\",
                apiName: \"otherMarkers\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.apiName' => ['The selected API name is invalid.'],
    ]]]]]);
});

test('a marker api name cannot be automatically created to an already existing one', function () {
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    addGroup($mapping, $markerGroup);

    $this->be($user);
    postCreateMappingMarkerGroup($mapping, $markerGroup);

    expect($mapping->fresh()->markerGroups->last()->apiName)->toBe('markers2');
});

test('a marker api name cannot be created with an already existing one', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            createMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                name: \"Markers\",
                group: \"$markerGroup->global_id\",
                apiName: \"markers\"
            }) {
                code
                mapping { id }
            }
        }
    ")->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.apiName' => ['The selected API name is invalid.'],
    ]]]]]);
});

test('creating marker options creates an action', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'My mapping']);
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);

    $this->be($user);
    postCreateMappingMarkerGroup($mapping, $markerGroup);

    expect($mapping->latestAction->type->is(MappingActionType::ADD_MAPPING_TAG_GROUP()))->toBeTrue()
        ->and($mapping->latestAction->description(false))->toBe('Added "Markers" marker group to "My mapping" page.');
});

test('changing marker options creates an action', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'My mapping']);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            updateMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\",
                name: \"Other markers\"
                type: STATUS
            }) {
                code
                mapping { id }
            }
        }
    ")->assertSuccessfulGraphQL();

    expect($mapping->latestAction->type->is(MappingActionType::CHANGE_MAPPING_TAG_GROUP()))->toBeTrue()
        ->and($mapping->latestAction->description(false))->toBe('Changed "Other markers" marker group on "My mapping" page.');
});

test('removing marker options creates an action', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'My mapping']);
    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    $options = addGroup($mapping, $markerGroup);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingMarkerGroup(input: {
                mappingId: \"$mapping->global_id\",
                id: \"$options->id\"
            }) {
                code
                mapping { id }
            }
        }
    ");

    expect($mapping->latestAction->type->is(MappingActionType::REMOVE_MAPPING_TAG_GROUP()))->toBeTrue()
        ->and($mapping->latestAction->description(false))->toBe('Removed "Markers" marker group from "My mapping" page.');
});

// Helpers
function postCreateMappingMarkerGroup(Model|bool|Mapping $mapping, MarkerGroup $markerGroup, ?Relationship $fromRelationship = null)
{
    return test()->postGraphQL([
        'query' => 'mutation UpdateMarkers($input: CreateMappingMarkerGroupInput!) {
            createMappingMarkerGroup(input: $input) {
                code
                mapping { id }
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'name' => 'Markers',
                'group' => $markerGroup->globalId(),
                'relationship' => $fromRelationship?->id(),
            ],
        ],
    ])->assertSuccessfulGraphQL();
}

function addGroup(Mapping $mapping, MarkerGroup $markerGroup, $attributes = []): ?AttributeCollectionItem
{
    $options = array_merge(
        ['name' => 'Markers', 'group' => $markerGroup],
        $attributes
    );
    $group = $mapping->addMarkerGroup($options);

    if ($attributes['relationship'] ?? false) {
        $options['id'] = $group->id;
        $attributes['relationship']->toMapping()->addMarkerGroup($options);
    }

    return $group;
}

function addRelationship($fromMapping, $toMapping, $type = null, $inverse = false, $attributes = [])
{
    $type = $type ?: RelationshipType::ONE_TO_MANY;
    $fromRelationship = $fromMapping->addRelationship(
        array_merge(['name' => 'relationship', 'type' => $type, 'to' => $toMapping], $attributes)
    );

    if ($inverse) {
        $toMapping->addRelationship(
            array_merge([
                'id' => $fromRelationship->id(),
                'name' => 'inverse',
                'type' => $type->inverse(),
                'to' => $fromMapping,
                'inverse' => true,
            ], $attributes)
        );
    }

    return $fromRelationship;
}
