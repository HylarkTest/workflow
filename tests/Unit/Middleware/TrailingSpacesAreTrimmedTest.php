<?php

declare(strict_types=1);

use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lighthouse allows strings with trailing spaces', function () {
    $user = createUser();
    $this->withGraphQLExceptionHandling();

    $mapping = createMapping($user, [
        'apiName' => 'people',
        'apiSingularName' => 'person',
        'fields' => [
            [
                'name' => 'Full name',
                'type' => 'SYSTEM_NAME',
            ],
            [
                'id' => 'email',
                'name' => 'Email',
                'type' => 'EMAIL',
                'options' => ['labeled' => ['freeText' => true]],
            ],
        ],
    ]);

    $this->be($user)->graphQL(
        'mutation CreatePerson($input: PersonItemCreateInput!) {
            items {
                people {
                    createPerson(input: $input) {
                        code
                    }
                }
            }
        }',
        ['input' => ['data' => [
            'fullName' => ['fieldValue' => 'Bro'],
            'email' => ['fieldValue' => ' me@email.com', 'label' => 'Main'],
        ]]]
    )->assertSuccessfulGraphQL();

    expect($mapping->items->first()->data['email'][Field::VALUE])->toBe('me@email.com');
});
