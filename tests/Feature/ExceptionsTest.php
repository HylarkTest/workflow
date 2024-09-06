<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('if a model is not found it throws an error', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $fakeId = base64_encode('Pinboard:123');

    $this->be($user)
        ->graphQL("{
            pinboard(id: \"$fakeId\") { id }
        }")
        ->assertJson(['errors' => [[
            'message' => "No results for the requested node(s) [$fakeId].",
            'extensions' => ['category' => 'missing'],
        ]]]);
});
