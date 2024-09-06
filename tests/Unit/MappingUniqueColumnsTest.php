<?php

declare(strict_types=1);

use Mappings\Models\Mapping;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('when two mappings are created with the same name a different api name is created', function () {
    $user = createUser();
    for ($i = 0; $i < 3; $i++) {
        createMapping($user, [
            'name' => 'Rebel Alliance',
            'singular_name' => 'Rebel',
        ]);
    }

    $mappings = Mapping::all('api_name', 'api_singular_name');
    expect($mappings->pluck('api_name')->all())->toBe(['rebelAlliance', 'rebelAlliance2', 'rebelAlliance3']);
    expect($mappings->pluck('api_singular_name')->all())->toBe(['rebel', 'rebel2', 'rebel3']);
});
