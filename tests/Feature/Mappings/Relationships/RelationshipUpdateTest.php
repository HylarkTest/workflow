<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Relationships\RelationshipType;

uses(RefreshDatabase::class);

test('relationships can be saved on a mapping', function () {
    $user = createUser();
    $fromMapping = createMapping($user);
    $toMapping = createMapping($user);

    $this->be($user)->assertGraphQLMutation(
        [
            'createMappingRelationship(input: $input)' => [
                'code' => '200',
                'success' => true,
                'mapping' => [
                    'id' => $fromMapping->global_id,
                    'relationships' => [[
                        'id' => new Ignore,
                        'name' => 'Related',
                        'apiName' => 'related',
                        'type' => 'ONE_TO_MANY',
                        'isInverse' => false,
                        'to' => ['id' => $toMapping->global_id],
                        'inverse' => [
                            'id' => new Ignore,
                            'name' => 'Related',
                            'apiName' => 'related',
                            'type' => 'MANY_TO_ONE',
                            'isInverse' => true,
                        ],
                    ]],
                ],
            ],
        ],
        ['input: CreateMappingRelationshipInput!' => [
            'mappingId' => $fromMapping->global_id,
            'name' => 'Related',
            'inverseName' => 'Related',
            'type' => 'ONE_TO_MANY',
            'to' => $toMapping->global_id,
        ]]
    );

    $fromRelationship = $fromMapping->fresh()->relationships->first();
    static::assertSame([
        'id' => $fromRelationship->id,
        'name' => 'Related',
        'apiName' => 'related',
        'to' => (string) $toMapping->getKey(),
        'createdAt' => $fromRelationship->createdAt,
        'updatedAt' => $fromRelationship->updatedAt,
        'type' => RelationshipType::ONE_TO_MANY->value,
        'inverse' => false,
    ], $fromRelationship->toArray());
    $toRelationship = $toMapping->fresh()->relationships->first();
    static::assertSame([
        'id' => $fromRelationship->id,
        'name' => 'Related',
        'apiName' => 'related',
        'to' => (string) $fromMapping->getKey(),
        'createdAt' => $toRelationship->createdAt,
        'updatedAt' => $toRelationship->updatedAt,
        'type' => RelationshipType::MANY_TO_ONE->value,
        'inverse' => true,
    ], $toRelationship->toArray());
});

test('relationship inverses have the correct type', function () {
    $user = createUser();
    $fromMapping = createMapping($user);
    $toMapping = createMapping($user);

    $this->be($user)->assertGraphQLMutation(
        'createMappingRelationship(input: $input)',
        ['input: CreateMappingRelationshipInput!' => [
            'mappingId' => $fromMapping->global_id,
            'name' => 'Related',
            'type' => 'MANY_TO_MANY',
            'to' => $toMapping->global_id,
        ]]
    );

    $fromRelationship = $fromMapping->fresh()->relationships->first();
    $toRelationship = $toMapping->fresh()->relationships->first();
    expect($fromRelationship->type)->toBe(RelationshipType::MANY_TO_MANY);
    expect($toRelationship->type)->toBe(RelationshipType::MANY_TO_MANY);

    $this->be($user)->assertGraphQLMutation(
        'createMappingRelationship(input: $input)',
        ['input: CreateMappingRelationshipInput!' => [
            'mappingId' => $fromMapping->global_id,
            'name' => 'Related 2',
            'type' => 'ONE_TO_ONE',
            'to' => $toMapping->global_id,
        ]]
    );

    $fromRelationship = $fromMapping->fresh()->relationships->last();
    $toRelationship = $toMapping->fresh()->relationships->last();
    expect($fromRelationship->type)->toBe(RelationshipType::ONE_TO_ONE);
    expect($toRelationship->type)->toBe(RelationshipType::ONE_TO_ONE);
});

test('default names are added if not specified', function () {
    $user = createUser();
    $fromMapping = createMapping($user, ['name' => 'Cats', 'singular_name' => 'Cat', 'api_name' => 'cats', 'api_singular_name' => 'cat']);
    $toMapping = createMapping($user, ['name' => 'Dogs', 'singular_name' => 'Dog', 'api_name' => 'dogs', 'api_singular_name' => 'dog']);

    $this->be($user)->graphQL("
    mutation {
        first: createMappingRelationship(input: {
            mappingId: \"$fromMapping->global_id\"
            type: ONE_TO_MANY,
            to: \"$toMapping->global_id\"
        }) { code }
        second: createMappingRelationship(input: {
            mappingId: \"$fromMapping->global_id\"
            type: MANY_TO_ONE,
            to: \"$toMapping->global_id\"
        }) { code }
    }
    ");

    $fromMapping = $fromMapping->fresh();
    $toMapping = $toMapping->fresh();
    $firstRelationship = $fromMapping->relationships->first();
    $secondRelationship = $fromMapping->relationships->last();
    $firstInverseRelationship = $toMapping->relationships->first();
    $secondInverseRelationship = $toMapping->relationships->last();

    static::assertSame([
        'name' => 'Dogs',
        'apiName' => 'dogs',
    ], Arr::only($firstRelationship->toArray(), ['name', 'apiName']));
    static::assertSame([
        'name' => 'Dog',
        'apiName' => 'dog',
    ], Arr::only($secondRelationship->toArray(), ['name', 'apiName']));
    static::assertSame([
        'name' => 'Cat',
        'apiName' => 'cat',
    ], Arr::only($firstInverseRelationship->toArray(), ['name', 'apiName']));
    static::assertSame([
        'name' => 'Cats',
        'apiName' => 'cats',
    ], Arr::only($secondInverseRelationship->toArray(), ['name', 'apiName']));
});

test('a relationship can be updated', function () {
    $user = createUser();
    $toMapping = createMapping($user);
    $fromMapping = createMapping($user, [
        'relationships' => [[
            'id' => 'related',
            'apiName' => 'related',
            'name' => 'Related',
            'type' => 'ONE_TO_MANY',
            'to' => $toMapping->getKey(),
        ]],
    ]);

    $this->be($user)->graphQL("
    mutation {
        updateMappingRelationship(input: {
            mappingId: \"$fromMapping->global_id\",
            id: \"related\",
            name: \"Children\",
        }) { code }
    }
    ")->assertSuccessfulGraphQL();

    $fromRelationship = $fromMapping->fresh()->relationships->first();
    static::assertSame([
        'id' => $fromRelationship->id,
        'name' => 'Children',
        'apiName' => 'related',
        'to' => (string) $toMapping->id,
        'createdAt' => $fromRelationship->createdAt,
        'updatedAt' => $fromRelationship->updatedAt,
        'type' => RelationshipType::ONE_TO_MANY->value,
        'inverse' => false,
    ], $fromRelationship->toArray());
});

test('a relationship can be deleted', function () {
    $user = createUser();
    $toMapping = createMapping($user);
    $fromMapping = createMapping($user);

    $relationship = $fromMapping->addRelationship([
        'type' => RelationshipType::ONE_TO_MANY,
        'to' => $toMapping,
        'name' => 'Related',
    ]);

    $this->be($user)->graphQL("
    mutation {
        deleteMappingRelationship(input: {
            mappingId: \"$fromMapping->global_id\",
            id: \"$relationship->id\"
        }) { code }
    }
    ")->assertSuccessfulGraphQL();

    expect($fromMapping->fresh()->relationships)->toBeEmpty();
    expect($toMapping->fresh()->relationships)->toBeEmpty();
});

test('relationships cannot be added with the same api name', function () {
    $user = createUser();
    $fromMapping = createMapping($user);
    $toMapping = createMapping($user);
    $fromMapping->addRelationship([
        'type' => RelationshipType::ONE_TO_MANY,
        'to' => $toMapping,
        'name' => 'Related',
    ]);

    $this->be($user)->graphQL("
    mutation {
        createMappingRelationship(input: {
            mappingId: \"$fromMapping->global_id\",
            name: \"Related\",
            type: ONE_TO_MANY,
            to: \"$toMapping->global_id\"
        }) {
            code
        }
    }
    ");

    $fromRelationship = $fromMapping->fresh()->relationships->last();
    expect($fromRelationship->apiName)->toBe('related2');
});
