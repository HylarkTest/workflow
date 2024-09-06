<?php

declare(strict_types=1);

use Mappings\Models\Mapping;
use Illuminate\Testing\TestResponse;
use Mappings\Core\Mappings\MappingType;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('a mapping can be created', function () {
    $user = createUser();
    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'Rebel Alliance',
        'singularName' => 'Rebel',
        'description' => 'A test description',
        'type' => MappingType::PERSON,
        'fields' => [['name' => 'Name', 'type' => FieldType::NAME()]],
    ]);

    /** @var \Mappings\Models\Mapping $mapping */
    $mapping = Mapping::query()->first();
    expect($mapping->name)->toBe('Rebel Alliance')
        ->and($mapping->api_name)->toBe('rebelAlliance')
        ->and($mapping->singular_name)->toBe('Rebel')
        ->and($mapping->api_singular_name)->toBe('rebel')
        ->and($mapping->description)->toBe('A test description')
        ->and(MappingType::PERSON)->toBe($mapping->type);
});

test('the created mapping fields cannot be too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => str_repeat('a', Mapping::MAX_TITLE_LENGTH + 1),
        'singularName' => str_repeat('a', Mapping::MAX_TITLE_LENGTH + 1),
        'description' => str_repeat('a', Mapping::MAX_DESCRIPTION_LENGTH + 1),
        'fields' => [['name' => 'Name', 'type' => FieldType::NAME()]],
    ])->assertJsonStructure([
        'errors' => [['extensions' => ['validation' => [
            'input.name', 'input.singularName', 'input.description',
        ]]]],
    ]);
});

test('the singular and plural api names are different', function () {
    $user = createUser();
    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'Rebellion',
        'singularName' => 'Rebellion',
        'fields' => [['name' => 'Name', 'type' => FieldType::NAME()]],
    ]);

    /** @var \Mappings\Models\Mapping $mapping */
    $mapping = Mapping::query()->first();
    expect($mapping->api_name)->toBe('rebellion')
        ->and($mapping->api_singular_name)->toBe('rebellionItem');
});

test('mappings cannot be created with the same api names', function () {
    $user = createUser();
    createMapping($user, ['name' => 'People']);
    $deletedMapping = createMapping($user, ['name' => 'People']);
    $deletedMapping->delete();

    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'People',
        'fields' => [['name' => 'Name', 'type' => FieldType::SYSTEM_NAME()]],
    ])->assertSuccessfulGraphQL();

    $base = $user->firstPersonalBase()->fresh();
    expect($base->mappings)->toHaveCount(2)
        ->and($base->mappings->first()->apiName)->toBe('people')
        ->and($base->mappings->first()->apiSingularName)->toBe('person')
        ->and($base->mappings->last()->apiName)->toBe('people3')
        ->and($base->mappings->last()->apiSingularName)->toBe('person3');
});

test('mappings cannot be created with singular names the same as plural names', function () {
    $user = createUser();
    createMapping($user, ['name' => 'People']);

    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'Person',
        'singularName' => 'People',
        'fields' => [['name' => 'Name', 'type' => FieldType::SYSTEM_NAME()]],
    ])->assertSuccessfulGraphQL();

    $base = $user->firstPersonalBase()->fresh();
    expect($base->mappings)->toHaveCount(2)
        ->and($base->mappings->first()->apiName)->toBe('people')
        ->and($base->mappings->first()->apiSingularName)->toBe('person')
        ->and($base->mappings->last()->apiName)->toBe('person2')
        ->and($base->mappings->last()->apiSingularName)->toBe('people2');
});

test('mappings cannot be created with too many fields', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $this->be($user);
    createMappingRequest([
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'Person',
        'singularName' => 'People',
        'fields' => [
            ['name' => 'Name', 'type' => FieldType::SYSTEM_NAME()],
            ...array_map(fn ($num) => ['name' => 'Line'.$num, 'type' => FieldType::LINE()], range(1, 40)),
            ['name' => 'Multi', 'type' => FieldType::MULTI(), 'options' => ['fields' => array_map(fn ($num) => ['name' => 'Line'.$num, 'type' => FieldType::LINE()], range(1, 10))]],
        ],
    ])->assertJson(['errors' => [['extensions' => ['validation' => ['input.fields' => ['The input.fields must not have more than 50 items.']]]]]]);
});

test('a mapping field cannot be added if it exceeds the limit', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $mapping = createMapping($user, [
        'name' => 'People',
        'fields' => [
            ['name' => 'Name', 'type' => FieldType::SYSTEM_NAME()],
            ...array_map(fn ($num) => ['name' => 'Line'.$num, 'type' => FieldType::LINE()], range(1, 49)),
        ],
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'createMappingField(input: $input)',
        ['input: CreateMappingFieldInput!' => [
            'mappingId' => $mapping->global_id,
            'name' => 'Line51',
            'type' => FieldType::LINE(),
        ]],
    )->assertGraphQLValidationError('limit', 'You have reached the limit for this account.');
});

test('a mapping multi field cannot be added if it exceeds the limit', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $mapping = createMapping($user, [
        'name' => 'People',
        'fields' => [
            ['name' => 'Name', 'type' => FieldType::SYSTEM_NAME()],
            ...array_map(fn ($num) => ['name' => 'Line'.$num, 'type' => FieldType::LINE()], range(1, 48)),
        ],
    ]);

    $this->be($user)->graphQL(
        'mutation AddMappingField($input: CreateMappingFieldInput!) {
            createMappingField(input: $input) { mapping { id } }
        }',
        ['input' => [
            'mappingId' => $mapping->global_id,
            'name' => 'Line51',
            'type' => FieldType::MULTI(),
            'options' => ['fields' => [
                ['name' => 'Line52', 'type' => FieldType::LINE()],
                ['name' => 'Line53', 'type' => FieldType::LINE()],
            ]],
        ]],
    )->assertJson(['errors' => [['extensions' => ['validation' => ['limit' => ['You have reached the limit for this account.']]]]]]);
});

// Helpers
function createMappingRequest($body): TestResponse
{
    return test()->graphQl(
        'mutation CreateMapping($mapping: MappingCreateInput!) {
            createMapping(input: $mapping) { mapping { id } }
        }',
        [
            'mapping' => $body,
        ],
    );
}
