<?php

declare(strict_types=1);

use Mappings\Models\Mapping;
use Illuminate\Testing\TestResponse;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('a field can be added to a mapping', function () {
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user);
    addFieldRequest($mapping, [
        'name' => 'Headline',
        'type' => FieldType::LINE()->key,
        'options' => '[]',
        'meta' => json_encode(['description' => 'Advertise yourself']),
    ]);

    $mapping = $mapping->fresh();
    expect($mapping->fields)->toHaveCount(3);

    /** @var \Mappings\Core\Mappings\Fields\Field $field */
    $field = $mapping->fields->last();
    expect($field->name)->toBe('Headline');
    expect($field->type()->is(FieldType::LINE()))->toBeTrue();
    expect($field->options)->toBeEmpty();
    expect($field->meta)->toBe(['description' => 'Advertise yourself']);
});

test('a field name cannot be too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user);
    addFieldRequest($mapping, [
        'name' => str_repeat('a', Field::MAX_NAME_LENGTH + 1),
        'type' => FieldType::LINE()->key,
        'options' => '[]',
    ])->assertJsonStructure(['errors' => [['extensions' => ['validation' => [
        'input.name',
    ]]]]]);
});

test('a field can be updated', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            [
                'name' => 'Name',
                'type' => FieldType::NAME()->value,
            ],
            [
                'name' => 'Headline',
                'type' => FieldType::LINE()->value,
                'options' => [],
            ],
        ],
    ]);

    $this->be($user);
    updateFieldRequest($mapping, $mapping->fields->last(), [
        'name' => 'Slogan',
        'options' => [],
        'meta' => json_encode(['description' => 'Advertise yourself more'], \JSON_THROW_ON_ERROR),
    ]);

    $mapping = $mapping->fresh();
    expect($mapping->fields)->toHaveCount(2);

    /** @var \Mappings\Core\Mappings\Fields\Field $field */
    $field = $mapping->fields->last();
    expect($field->name)->toBe('Slogan')
        ->and($field->meta)->toBe(['description' => 'Advertise yourself more']);
});

test('a field cannot be updated with a name too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [[
            'name' => 'Headline',
            'type' => FieldType::LINE()->value,
            'options' => [],
        ]],
    ]);

    $this->be($user);
    updateFieldRequest($mapping, $mapping->fields->last(), [
        'name' => str_repeat('a', Field::MAX_NAME_LENGTH + 1),
    ])->assertJsonStructure(['errors' => [['extensions' => ['validation' => [
        'input.name',
    ]]]]]);
});

test('a field cannot be added with the name of an existing field', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [[
            'apiName' => 'headline',
            'name' => 'Headline',
            'type' => FieldType::LINE()->value,
            'options' => [],
        ]],
    ]);

    $this->be($user);
    addFieldRequest($mapping, [
        'apiName' => 'headline',
        'name' => 'headline',
        'type' => FieldType::LINE()->key,
        'options' => '[]',
    ])->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.name' => ['The name has already been taken.'],
        'input.apiName' => ['The API name has already been taken.'],
    ]]]]]);
});

test('a field api name is automatically made unique', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            [
                'name' => 'Headline',
                'type' => FieldType::LINE()->value,
                'options' => [],
            ],
            [
                'name' => '1 Field',
                'type' => FieldType::LINE()->value,
                'options' => [],
            ],
        ],
    ]);

    $this->be($user);
    addFieldRequest($mapping, [
        'name' => 'Headline!',
        'type' => FieldType::LINE()->key,
        'options' => '[]',
    ]);
    addFieldRequest($mapping, [
        'name' => '2 Field',
        'type' => FieldType::LINE()->key,
        'options' => '[]',
    ]);

    expect($mapping->fresh()->fields->map->apiName->all())->toBe([
        'headline',
        '_1field',
        'headline2',
        '_2field',
    ]);
});

test('a field api name cannot be updated to one that already exists', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            [
                'id' => 'name',
                'apiName' => 'name',
                'name' => 'Name',
                'type' => FieldType::NAME()->value,
            ],
            [
                'id' => 'headline',
                'apiName' => 'headline',
                'name' => 'Headline',
                'type' => FieldType::LINE()->value,
                'options' => [],
            ],
        ],
    ]);

    $this->be($user);
    updateFieldRequest($mapping, $mapping->fields->last(), [
        'name' => 'Headline',
        'apiName' => 'name',
    ])->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.apiName' => ['The API name has already been taken.'],
    ]]]]]);
});

test('default fields cannot be deleted', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->postGraphQL([
        'query' => 'mutation DeleteField($input: DeleteMappingFieldInput!) {
            deleteMappingField(input: $input) {
                code
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $mapping->fields->first()->id,
            ],
        ],
    ])->assertJson(['errors' => [['message' => 'The system name field cannot be removed.']]]);
});

test('a field can be deleted', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            [
                'name' => 'Name',
                'type' => FieldType::NAME()->value,
            ],
            [
                'name' => 'Headline',
                'type' => FieldType::LINE()->value,
                'options' => [],
            ],
        ],
    ]);

    $this->be($user)->postGraphQL([
        'query' => 'mutation DeleteField($input: DeleteMappingFieldInput!) {
            deleteMappingField(input: $input) {
                code
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $mapping->fields->first()->id,
            ],
        ],
    ]);

    $mapping = $mapping->fresh();
    expect($mapping->fields)->toHaveCount(1);
});

// Helpers
function addFieldRequest(Mapping $mapping, $body): TestResponse
{
    return test()->postGraphQL([
        'query' => 'mutation CreateField($input: CreateMappingFieldInput!) {
            createMappingField(input: $input) {
                code
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                ...$body,
            ],
        ],
    ]);
}

function updateFieldRequest(Mapping $mapping, Field $field, $body): TestResponse
{
    return test()->postGraphQL([
        'query' => 'mutation UpdateField($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) {
                code
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $field->id,
                ...$body,
            ],
        ],
    ]);
}
