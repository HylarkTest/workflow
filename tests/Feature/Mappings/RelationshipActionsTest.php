<?php

declare(strict_types=1);

use App\Models\Mapping;
use App\Core\MappingActionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Mappings\Core\Mappings\Relationships\RelationshipType;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

test('creating a relationship creates an action', function () {
    $this->withoutGraphQLExceptionHandling();
    config(['actions.mandatory_performer' => false]);
    config(['actions.automatic' => true]);
    $user = createUser();
    $fromMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);

    $this->be($user)->graphQL("
        mutation {
            createMappingRelationship(input: {
                mappingId: \"$fromMapping->global_id\"
                type: ONE_TO_MANY
                to: \"$toMapping->global_id\"
            }) {
                code
            }
        }
    ")->assertSuccessfulGraphQL();

    $latestAction = $fromMapping->latestAction;
    $toMappingLatestAction = $toMapping->latestAction;

    expect($latestAction->type->is(MappingActionType::ADD_MAPPING_RELATIONSHIP()))->toBeTrue();
    expect($latestAction->description())->toBe('Added "Children" relationship to "Parents" page by '.$user->name.'.');
    static::assertSame([
        [
            'description' => 'Added the name',
            'before' => null,
            'after' => 'Children',
            'type' => 'line',
        ],
        [
            'description' => 'Set related blueprint',
            'before' => null,
            'after' => 'Children',
            'type' => 'line',
        ],
        [
            'description' => 'Added the type',
            'before' => null,
            'after' => 'One to many',
            'type' => 'line',
        ],
    ], $this->resolveDeferred($latestAction->changes()));

    expect($toMappingLatestAction->type->is(MappingActionType::ADD_MAPPING_RELATIONSHIP()))->toBeTrue();
    expect($toMappingLatestAction->description())->toBe('Added "Parent" relationship to "Children" page by '.$user->name.'.');
});

//test('fetching several relationship actions doesnt run unnecessary queries', function () {
//    config(['actions.mandatory_performer' => false]);
//    $user = createUser();
//    $fromMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
//    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
//
//    $retrievalCount = 0;
//
//    Mapping::retrieved(static function (Mapping $mapping) use ($toMapping, &$retrievalCount) {
//        $mapping->is($toMapping) ? $retrievalCount++ : null;
//    });
//
//    $this->be($user)->graphQL("
//        mutation {
//            first: createMappingRelationship(input: { mappingId: \"$fromMapping->global_id\", type: ONE_TO_MANY, to: \"$toMapping->global_id\" }) { code }
//            second: createMappingRelationship(input: { mappingId: \"$fromMapping->global_id\", type: ONE_TO_MANY, to: \"$toMapping->global_id\" }) { code }
//        }
//    ")->assertSuccessful();
//
//    expect($retrievalCount)->toBe(1);
//});

test('changing a relationship creates an action', function () {
    config(['actions.mandatory_performer' => false]);
    config(['actions.automatic' => true]);
    $user = createUser();
    $fromMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $relationship = $fromMapping->addRelationship([
        'name' => 'Children',
        'type' => RelationshipType::ONE_TO_MANY,
        'to' => $toMapping,
    ]);

    $this->be($user)->graphQL("
        mutation {
            updateMappingRelationship(input: {
                mappingId: \"$fromMapping->global_id\",
                id: \"$relationship->id\",
                name: \"Offspring\",
            }) { code }
        }
    ");

    expect($fromMapping->latestAction->type->is(MappingActionType::CHANGE_MAPPING_RELATIONSHIP()))->toBeTrue();
    expect($fromMapping->latestAction->description())->toBe('Changed "Offspring" relationship on "Parents" page by '.$user->name.'.');
    static::assertSame([[
        'description' => 'Changed the name',
        'before' => 'Children',
        'after' => 'Offspring',
        'type' => 'line',
    ]], $fromMapping->latestAction->changes());
});

test('removing a relationship creates an action', function () {
    config(['actions.mandatory_performer' => false]);
    config(['actions.automatic' => true]);
    $user = createUser();
    $fromMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $relationship = $fromMapping->addRelationship([
        'name' => 'Children',
        'type' => RelationshipType::ONE_TO_MANY,
        'to' => $toMapping,
    ]);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingRelationship(input: { mappingId: \"$fromMapping->global_id\", id: \"$relationship->id\" }) { code }
        }
    ");

    expect($fromMapping->latestAction->type->is(MappingActionType::REMOVE_MAPPING_RELATIONSHIP()))->toBeTrue();
    expect($fromMapping->latestAction->description())->toBe('Removed "Children" relationship from "Parents" page by '.$user->name.'.');
    expect($fromMapping->latestAction->changes())->toBeNull();
});
