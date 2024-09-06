<?php

declare(strict_types=1);

use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Sections\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

test('a mapping can have sections', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'education']],
    ]);

    $this->be($user)->graphQL("
        query {
            mapping(id: \"$mapping->global_id\") {
                sections { id name }
            }
        }
    ")->assertJson(['data' => ['mapping' => ['sections' => [
        ['id' => 'abc', 'name' => 'education'],
    ]]]]);
});

test('a section can be added', function () {
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->graphQL("
        mutation {
            createMappingSection(input: { mappingId: \"$mapping->global_id\", name: \"education\" }) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->sections)->toHaveCount(1);
    expect($mapping->sections->first()->name)->toBe('education');
});

test('the sections name cannot be too long', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);
    $name = str_repeat('a', Section::MAX_LENGTH + 1);

    $this->be($user)->graphQL("
        mutation {
            createMappingSection(input: { mappingId: \"$mapping->global_id\", name: \"$name\" }) {
                mapping { id }
            }
        }
    ")->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.name' => ['The name must not be greater than 50 characters.'],
    ]]]]]);
});

test('a section can be changed', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'profile']],
    ]);

    $this->be($user)->graphQL("
        mutation {
            updateMappingSection(input: { mappingId: \"$mapping->global_id\", id: \"abc\", name: \"education\" }) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->sections)->toHaveCount(1);
    expect($mapping->sections->first()->name)->toBe('education');
});

test('a section can be removed', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'profile']],
        'fields' => [['id' => 'job', 'name' => 'Job', 'type' => FieldType::LINE(), 'section' => 'abc']],
    ]);

    $this->be($user)->graphQL("
        mutation {
            deleteMappingSection(input: { mappingId: \"$mapping->global_id\", id: \"abc\" }) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->sections)->toBeEmpty();
    expect($mapping->fields->find('job')->section)->toBeNull();
});

test('a field can be added with a section', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'profile']],
    ]);

    $this->be($user)->graphQL("
        mutation {
            createMappingField(input: { mappingId: \"$mapping->global_id\", name: \"job\", type: LINE, section: \"abc\"}) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->fields->last()->section)->toBe('abc');
});

test('a section can be changed on a field', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'profile'], ['id' => 'def', 'name' => 'education']],
        'fields' => [['id' => 'job', 'name' => 'job', 'type' => FieldType::LINE(), 'section' => 'abc']],
    ]);

    $this->be($user)->graphQL("
        mutation {
            updateMappingField(input: { mappingId: \"$mapping->global_id\", id: \"job\", section: \"def\"}) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->fields->last()->section)->toBe('def');
});

test('a section can be removed from a field', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [['id' => 'abc', 'name' => 'profile'], ['id' => 'def', 'name' => 'education']],
        'fields' => [['id' => 'job', 'name' => 'job', 'type' => FieldType::LINE(), 'section' => 'abc']],
    ]);

    $this->be($user)->graphQL("
        mutation {
            updateMappingField(input: { mappingId: \"$mapping->global_id\", id: \"job\", section: null }) {
                mapping { id }
            }
        }
    ");

    $mapping = $mapping->fresh();
    expect($mapping->fields->last()->section)->toBeNull();
});

test('fields can be fetched by section', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'sections' => [
            ['id' => 'abc', 'name' => 'profile'],
            ['id' => 'def', 'name' => 'education'],
        ],
        'fields' => [
            ['id' => 'name', 'name' => 'Name', 'type' => FieldType::NAME()],
            ['id' => 'job', 'name' => 'Job', 'type' => FieldType::LINE(), 'section' => 'abc'],
            ['id' => 'uni', 'name' => 'University', 'type' => FieldType::LINE(), 'section' => 'def'],
            ['id' => 'phone', 'name' => 'Phone', 'type' => FieldType::LINE()],
        ],
    ]);

    $this->be($user)->graphQL("
        query {
            mapping(id: \"$mapping->global_id\") {
                profileFields: fields(sections: [\"abc\"]) { id }
                eduFields: fields(sections: [\"def\"]) { id }
                fields(sections: [null]) { id }
            }
        }
    ")->assertJson(['data' => ['mapping' => [
        'profileFields' => [['id' => 'job']],
        'eduFields' => [['id' => 'uni']],
        'fields' => [['id' => 'name'], ['id' => 'phone']],
    ]]]);
});
