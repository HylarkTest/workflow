<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Mappings\Core\Mappings\Relationships\RelationshipType;

uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

test('mappings can be fetched with data', function () {
    config(['actions.mandatory_performer' => false]);

    $user = createUser();

    $mapping = createMapping($user);
    $thatMapping = createMapping($user);
    $thisMapping = createMapping($user);

    $mapping->addRelationship([
        'type' => RelationshipType::ONE_TO_ONE,
        'to' => $thatMapping,
        'name' => 'That',
    ]);
    $mapping->addRelationship([
        'type' => RelationshipType::ONE_TO_ONE,
        'to' => $thisMapping,
        'name' => 'This',
    ]);

    $id = $mapping->globalId();
    /** @var \App\Core\Mappings\Features\Feature $feature */
    $this->be($user)->graphQL("
    {
        mapping(id: \"$id\") {
            id
            relationships {
                id
                name
                to {
                    id
                    name
                    singularName
                    type
                }
                inverse {
                    id
                    name
                }
            }
        }
    }
    ")->assertJson(['data' => [
        'mapping' => [
            'id' => $id,
        ],
    ]], true);
});
