<?php

declare(strict_types=1);

use App\Models\Mapping;
use Illuminate\Testing\TestResponse;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

$mappingFeatures = [
    MappingFeatureType::EVENTS,
    MappingFeatureType::TODOS,
    MappingFeatureType::NOTES,
    MappingFeatureType::PINBOARD,
    MappingFeatureType::DOCUMENTS,
    MappingFeatureType::TIMEKEEPER,
    MappingFeatureType::LINKS,
    MappingFeatureType::PRIORITIES,
    MappingFeatureType::FAVORITES,
    MappingFeatureType::EMAILS,
    //    MappingFeatureType::COLLABORATION,
    //    MappingFeatureType::COMMENTS,
    //    MappingFeatureType::GOALS,
    //    MappingFeatureType::HEALTH,
    //    MappingFeatureType::PLANNER,
    //    MappingFeatureType::STATISTICS,
];

test('a feature can be enabled on a mapping', function (MappingFeatureType $feature) {
    $user = createUser();
    $this->be($user);
    $mapping = createMapping($user, ['features' => []]);

    addOrUpdateFeatureRequest($mapping, [
        'val' => $feature->value,
        'options' => [],
    ]);

    $mapping = $mapping->fresh();
    expect($mapping->features)->toHaveCount(1)
        ->and($mapping->features->first()->type())->toBe($feature);
})->with($mappingFeatures);

test('a feature can be disabled on a mapping', function (MappingFeatureType $feature) {
    $user = createUser();
    $this->be($user);
    $mapping = createMapping($user, [
        'features' => [['val' => $feature->value, 'options' => []]],
    ]);

    $this->postGraphQL([
        'query' => 'mutation UpdateFeature($input: DeleteMappingFeatureInput!) {
            deleteMappingFeature(input: $input) {
                mapping { id }
            }
        }',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'val' => $feature->value,
            ],
        ],
    ])->assertSuccessfulGraphQL()->assertJson(['data' => [
        'deleteMappingFeature' => ['mapping' => ['id' => $mapping->globalId()]],
    ]]);

    $mapping->refresh();
    expect($mapping->features)->toBeEmpty();
})->with($mappingFeatures);

// Helpers
function addOrUpdateFeatureRequest(Mapping $mapping, $body): TestResponse
{
    return test()->postGraphQL([
        'query' => 'mutation UpdateFeature($feature: MappingFeatureInput!) {
            createOrUpdateMappingFeature(input: $feature) {
                mapping { id }
            }
        }',
        'variables' => [
            'feature' => array_merge(['mappingId' => $mapping->globalId()], $body),
        ],
    ])->assertSuccessfulGraphQL()->assertJson([
        'data' => [
            'createOrUpdateMappingFeature' => [
                'mapping' => ['id' => $mapping->globalId()],
            ],
        ],
    ]);
}
