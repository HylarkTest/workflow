<?php

declare(strict_types=1);

use App\Models\Note;
use MarkupUtils\Plaintext;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a feature count can be queried on an item', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'name' => 'Items',
        'features' => [['val' => MappingFeatureType::NOTES->value, 'options' => []]],
    ]);
    $item = createItem($mapping, ['name' => 'Item 1']);
    $user->firstPersonalBase()->createDefaultNotebooks();
    $notebook = $user->firstSpace()->notebooks->first();

    $note = Note::query()->forceCreate([
        'notebook_id' => $notebook->id,
        'text' => (new Plaintext('Note 1')),
    ]);
    Note::query()->forceCreate([
        'notebook_id' => $notebook->id,
        'text' => (new Plaintext('Note 2')),
    ]);
    $item->notes()->attach($note);

    $this->be($user)->graphQL(/** @lang GraphQL */ '
        query ($itemId: ID!) {
            items {
                item(id: $itemId) {
                    features {
                        notes {
                            pageInfo {
                                total
                            }
                        }
                    }
                }
            }
        }
    ', ['itemId' => $item->global_id])->assertJsonPath('data.items.item.features.notes.pageInfo.total', 1);
});

test('all features can be fetched on an item', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'name' => 'Items',
        'features' => [['val' => MappingFeatureType::NOTES->value, 'options' => []]],
    ]);

    $item = createItem($mapping, ['name' => 'Item 1']);
    $user->firstPersonalBase()->createDefaultNotebooks();
    $notebook = $user->firstSpace()->notebooks->first();

    $note1 = $notebook->notes()->create(['text' => (new Plaintext('Note 1'))]);
    $this->travel(1)->minutes();
    $note2 = $notebook->notes()->create(['text' => (new Plaintext('Note 2'))]);
    $this->travel(1)->minutes();
    $note1->update(['text' => (new Plaintext('Note 1 updated'))]);
    $notes = $note1->newCollection([$note1, $note2]);
    $item->notes()->attach($notes);

    $this->be($user)
        ->assertGraphQL(['items' => ['item(id: $itemId)' => ['features' => [
            'notes' => ['edges' => [
                ['node' => ['id' => $notes[1]->global_id]],
                ['node' => ['id' => $notes[0]->global_id]],
            ]],
            'latestNote: notes(orderBy: { field: CREATED_AT, direction: DESC }, first: 1)' => ['edges' => [
                ['node' => ['id' => $notes[1]->global_id]],
            ]],
            'lastUpdatedNote: notes(orderBy: { field: UPDATED_AT, direction: DESC }, first: 1)' => ['edges' => [
                ['node' => ['id' => $notes[0]->global_id]],
            ]],
        ]]]], ['itemId: ID!' => $item->global_id]);
});
