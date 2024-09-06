<?php

declare(strict_types=1);

use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an item can have a deadline set', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'Items']);
    $mapping->enableFeature(MappingFeatureType::TIMEKEEPER);
    $item = createItem($mapping);

    $this->be($user)->assertGraphQLMutation(
        'items.items.updateItem(input: $input).code',
        ['input: ItemItemUpdateInput!' => [
            'id' => $item->global_id,
            'startAt' => now()->addDay(),
        ]]
    );
});

test('the due by date cannot be before the start at', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'Items']);
    $mapping->enableFeature(MappingFeatureType::TIMEKEEPER);
    $item = createItem($mapping);

    $this->be($user)->assertFailedGraphQLMutation(
        'items.items.updateItem(input: $input).code',
        ['input: ItemItemUpdateInput!' => [
            'id' => $item->global_id,
            'startAt' => now()->addDay(),
            'dueBy' => now(),
        ]]
    )->assertGraphQLValidationError('input.dueBy', 'The due by must be a date after start at.');
});
