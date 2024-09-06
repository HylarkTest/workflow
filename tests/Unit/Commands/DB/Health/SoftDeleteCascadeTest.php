<?php

declare(strict_types=1);

use App\Models\Mapping;
use App\Core\Pages\PageType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// todo: make this more dynamic so any model that has cascade relationships are also tested
test('dependant children of soft delete models are picked up and also soft deleted', function () {
    $mapping = createMapping(createUser());
    $mapping->delete();

    $item = createItem($mapping);
    $page = $mapping->space->pages()->create([
        'name' => 'Test Page',
        'type' => PageType::ENTITIES,
        'mapping_id' => $mapping->id,
    ]);

    // assert that the mapping is soft deleted, but the dependant children are not
    expect($mapping->fresh()->deleted_at)->not->toBeNull()
        ->and($item->fresh()->deleted_at)->toBeNull()
        ->and($page->fresh()->deleted_at)->toBeNull();

    $this->artisan('db:health:soft-delete-cascade', ['--fix' => true, '--force' => true]);

    // assert that after command is run, the dependant children are also soft deleted
    expect($item->fresh()->deleted_at)->not->toBeNull()
        ->and($page->fresh()->deleted_at)->not->toBeNull();
});
