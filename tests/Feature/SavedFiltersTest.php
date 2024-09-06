<?php

declare(strict_types=1);

use App\Core\Groups\Role;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Core\Actions\ActionTypes\SavedFilterActionType;

uses(RefreshDatabase::class);

test('a user can save a filter on an entities page', function () {
    $user = createUser();
    $page = createEntitiesPage($user);
    enableAllActions();

    $this->be($user)->assertGraphQLMutation(
        'saveFilter',
        [
            'input: SaveFilterInput!' => [
                'nodeId' => $page->global_id,
                'name' => 'My filter',
                'private' => false,
                'filters' => [
                    'search' => 'ABC',
                ],
                'orderBy' => [['field' => 'name', 'direction' => 'ASC']],
                'group' => 'FAVORITE',
            ],
        ],
    );

    expect($page->savedFilters()->count())->toBe(1);

    $action = $page->latestAction;

    expect($action)
        ->type->toBe(SavedFilterActionType::SAVED_FILTER_CREATE())
        ->description(false)->toBe('Filter saved on Page "My page"')
        ->changes()->toBe([
            [
                'description' => 'Added the name',
                'before' => null,
                'after' => 'My filter',
                'type' => 'line',
            ],
        ]);

});

test('a user can save a filter on a list page', function () {
    $user = createUser();
    $todoList = createList($user, 'todoList', [], 1);
    $page = createListsPage($user, [
        'type' => 'TODOS',
        'lists' => [$todoList->global_id],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'saveFilter',
        [
            'input: SaveFilterInput!' => [
                'nodeId' => $page->global_id,
                'name' => 'My filter',
                'private' => false,
                'filters' => [
                    'search' => 'ABC',
                ],
                'orderBy' => [['field' => 'name', 'direction' => 'ASC']],
                'group' => 'FAVORITE',
            ],
        ],
    );

    expect($page->savedFilters()->count())->toBe(1);
});

test('a user cannot save a filter on an entity page', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $page = createEntityPage($user);

    $this->be($user)->assertFailedGraphQLMutation(
        'saveFilter(input: $input)',
        [
            'input: SaveFilterInput!' => [
                'nodeId' => $page->global_id,
                'name' => 'My filter',
                'private' => false,
                'filters' => [
                    'search' => 'ABC',
                ],
                'orderBy' => [['field' => 'name', 'direction' => 'ASC']],
                'group' => 'FAVORITE',
            ],
        ],
    );
});

test('a user can save a private filter', function () {
    $user = createCollabUser();
    /** @var \App\Models\Base $base */
    $base = tenant();
    $baseUser = $base->members()->find($user->id)->pivot;
    $otherUser = createCollabUser(Role::ADMIN, $base);
    $otherBaseUser = $base->members()->find($otherUser->id)->pivot;
    $page = createEntitiesPage($base);

    enableAllActions();
    $this->be($user)->assertGraphQLMutation(
        'saveFilter',
        [
            'input: SaveFilterInput!' => [
                'nodeId' => $page->global_id,
                'name' => 'My filter',
                'private' => true,
                'filters' => [
                    'search' => 'ABC',
                ],
                'orderBy' => [['field' => 'name', 'direction' => 'ASC']],
                'group' => 'FAVORITE',
            ],
        ],
    );

    expect($page->savedFilters)->toHaveCount(1);
    expect($page->savedFiltersForUser($baseUser)->get())->toHaveCount(1);
    expect($page->savedFiltersForUser($otherBaseUser)->get())->toHaveCount(0);

    $action = $page->latestAction()->withoutGlobalScopes()->getResults();
    expect($action)
        ->type->toBe(SavedFilterActionType::PRIVATE_SAVED_FILTER_CREATE())
        ->description(false)->toBe('Private filter saved on Page "My page"')
        ->is_private->toBeTrue();
});

test('a user can delete a filter', function () {
    $user = createCollabUser();
    $page = createEntitiesPage(tenant());

    enableAllActions();
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'deleteSavedFilter',
        [
            'input: DeleteSavedFilterInput!' => [
                'id' => $filter->global_id,
            ],
        ],
    );

    expect($filter->fresh())->toBeNull();

    $action = $page->latestAction;
    expect($action)
        ->type->toBe(SavedFilterActionType::SAVED_FILTER_DELETE())
        ->description(false)->toBe('Filter removed from Page "My page"');
});

test('a user can update a filter', function () {
    $user = createCollabUser();
    $page = createEntitiesPage(tenant());

    enableAllActions();
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'updateSavedFilter',
        [
            'input: UpdateSavedFilterInput!' => [
                'id' => $filter->global_id,
                'name' => 'Updated filter',
            ],
        ],
    );

    expect($filter->fresh())->name->toBe('Updated filter');

    $action = $page->latestAction;
    expect($action)
        ->type->toBe(SavedFilterActionType::SAVED_FILTER_UPDATE())
        ->description(false)->toBe('Filter updated on Page "My page"')
        ->changes()->toBe([
            [
                'description' => 'Changed the name',
                'before' => 'Test filter',
                'after' => 'Updated filter',
                'type' => 'line',
            ],
        ]);
});

test('a user can fetch filters on a page', function () {
    $user = createCollabUser();
    /** @var \App\Models\Base $base */
    $base = tenant();
    $mapping = createMapping($base);

    $page = createEntitiesPage($mapping);
    $otherPage = createEntitiesPage($mapping);

    /** @var \App\Models\SavedFilter $filter */
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $otherPage->savedFilters()->create([
        'name' => 'Other test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $this->be($user)->assertGraphQL([
        "savedFilters(nodeId: \"$page->global_id\")" => ['edges' => [
            ['node' => [
                'id' => $filter->global_id,
                'name' => 'Test filter',
                'filters' => new JSONField(['search' => 'ABC']),
                'group' => null,
                'private' => false,
                'orderBy' => new NullFieldWithSubQuery('{ field direction }'),
                'createdAt' => $filter->created_at->toIso8601String(),
                'updatedAt' => $filter->updated_at->toIso8601String(),
            ]],
        ]],
    ])->assertJsonCount(1, 'data.savedFilters.edges');
});

test('a user can fetch all public and private filters', function () {
    $user = createCollabUser();
    /** @var \App\Models\Base $base */
    $base = tenant();
    $baseUser = $base->members()->find($user->id)->pivot;
    $otherUser = createCollabUser(Role::ADMIN, $base);
    $otherBaseUser = $base->members()->find($otherUser->id)->pivot;

    $page = createEntitiesPage($base);

    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $privateFilter = $page->savedFilters()->create([
        'name' => 'Other test filter',
        'filters' => ['search' => 'ABC'],
        'base_user_id' => $baseUser->id,
    ]);

    $otherPrivateFilter = $page->savedFilters()->create([
        'name' => 'Other test filter',
        'filters' => ['search' => 'ABC'],
        'base_user_id' => $otherBaseUser->id,
    ]);

    $this->be($user)->assertGraphQL([
        'savedFilters(privacy: ALL)' => ['edges' => [
            ['node' => ['id' => $privateFilter->global_id]],
            ['node' => ['id' => $filter->global_id]],
        ]],
    ])->assertJsonCount(2, 'data.savedFilters.edges');
});

test('a user can fetch only public filters', function () {
    $user = createCollabUser();
    /** @var \App\Models\Base $base */
    $base = tenant();
    $baseUser = $base->members()->find($user->id)->pivot;
    $page = createEntitiesPage($base);

    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $privateFilter = $page->savedFilters()->create([
        'name' => 'Other test filter',
        'filters' => ['search' => 'ABC'],
        'base_user_id' => $baseUser->id,
    ]);

    $this->be($user)->assertGraphQL([
        'savedFilters(privacy: ONLY_PUBLIC)' => ['edges' => [
            ['node' => ['id' => $filter->global_id]],
        ]],
    ])->assertJsonCount(1, 'data.savedFilters.edges');
});

test('a user can fetch only private filters', function () {
    $user = createCollabUser();
    /** @var \App\Models\Base $base */
    $base = tenant();
    $baseUser = $base->members()->find($user->id)->pivot;
    $page = createEntitiesPage($base);

    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $privateFilter = $page->savedFilters()->create([
        'name' => 'Other test filter',
        'filters' => ['search' => 'ABC'],
        'base_user_id' => $baseUser->id,
    ]);

    $this->be($user)->assertGraphQL([
        'savedFilters(privacy: ONLY_PRIVATE)' => ['edges' => [
            ['node' => ['id' => $privateFilter->global_id]],
        ]],
    ])->assertJsonCount(1, 'data.savedFilters.edges');
});

test('a user can search filters', function () {
    $user = createUser();
    $page = createEntitiesPage($user);

    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $privateFilter = $page->savedFilters()->create([
        'name' => 'Other filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $this->be($user)->assertGraphQL([
        'savedFilters(search: "Test")' => ['edges' => [
            ['node' => ['id' => $filter->global_id]],
        ]],
    ])->assertJsonCount(1, 'data.savedFilters.edges');
});

test('a user can set a default filter on a page', function () {
    $user = createUser();
    $page = createEntitiesPage($user);
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    enableAllActions();
    $this->be($user)->assertGraphQLMutation(
        'updateEntitiesPage',
        [
            'input: UpdateEntitiesPageInput!' => [
                'id' => $page->global_id,
                'defaultFilterId' => $filter->global_id,
            ],
        ],
    );

    expect($page->refresh()->default_filter_id)->toBe($filter->id);

    $action = $page->latestAction;
    expect($this->resolveDeferred($action->changes()))->toBe([
        [
            'description' => 'Added the default filter',
            'before' => null,
            'after' => 'Test filter',
            'type' => 'line',
        ],
    ]);
});

test('a user can set a personal default filter on a page', function () {
    enableAllActions();
    $user = createUser();
    $page = createEntitiesPage($user);
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'updateEntitiesPage',
        [
            'input: UpdateEntitiesPageInput!' => [
                'id' => $page->global_id,
                'personalDefaultFilterId' => $filter->global_id,
            ],
        ],
    );

    expect($page->personalSettings()->first())
        ->settings->personalDefaultFilterId
        ->toBe($filter->id);

    /** @var \App\Models\Action $action */
    $action = $page->latestAction()->withoutGlobalScopes()->getResults();
    expect($action->is_private)->toBeTrue();
    expect($this->resolveDeferred($action->changes()))->toBe([
        [
            'description' => 'Added the personal default filter',
            'before' => null,
            'after' => 'Test filter',
            'type' => 'line',
        ],
    ]);
});

test('a user only sees their personal default filter', function () {
    $user = createCollabUser();
    $base = tenant();
    $otherUser = createCollabUser(Role::MEMBER, $base);
    $page = createEntitiesPage($base);
    $filterA = $page->savedFilters()->create([
        'name' => 'First user filter',
        'filters' => ['search' => 'ABC'],
    ]);
    $filterB = $page->savedFilters()->create([
        'name' => 'Second user filter',
        'filters' => ['search' => 'ABC'],
    ]);
    $page->getPersonalSettings($user->bases->find($base->id)->pivot)->updatePreferences(function ($preferences) use ($filterA) {
        $preferences->personalDefaultFilterId = $filterA->id;
    });
    $page->getPersonalSettings($otherUser->bases->find($base->id)->pivot)->updatePreferences(function ($preferences) use ($filterB) {
        $preferences->personalDefaultFilterId = $filterB->id;
    });

    tenancy()->initialize($base);
    $this->be($user)->graphQL(
        "query { page(id: \"$page->global_id\") { ...on EntitiesPage { personalDefaultFilter { id } } } }"
    )->assertJson(['data' => ['page' => ['personalDefaultFilter' => ['id' => $filterA->global_id]]]]);
    tenancy()->initialize($base);
    $this->be($otherUser)->graphQL(
        "query { page(id: \"$page->global_id\") { ...on EntitiesPage { personalDefaultFilter { id } } } }"
    )->assertJson(['data' => ['page' => ['personalDefaultFilter' => ['id' => $filterB->global_id]]]]);
});

test('a user cannot set a private filter as a public default filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $page = createEntitiesPage($user);
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
        'base_user_id' => $user->firstPersonalBase()->pivot->id,
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'updateEntitiesPage',
        [
            'input: UpdateEntitiesPageInput!' => [
                'id' => $page->global_id,
                'defaultFilterId' => $filter->global_id,
            ],
        ],
    )->assertGraphQLValidationError('input.defaultFilterId', 'The selected default filter is invalid.');
});

test('when a page is deleted the personal default filter is removed from preferences', function () {
    $user = createUser();
    $page = createEntitiesPage($user);
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);

    $user->firstPersonalBase()->pivot->updatePreferences(function ($preferences) use ($page, $filter) {
        $preferences->pageSettings[$page->id] = ['defaultFilter' => $filter->id];
    });

    $page->delete();

    expect($user->firstPersonalBase()->pivot->fresh()->settings)
        ->pageSettings
        ->not->toHaveKey($page->id);
});

test('when a saved filter is deleted it is removed as a default filter from pages and personal preferences', function () {
    $user = createUser();
    $page = createEntitiesPage($user);
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => ['search' => 'ABC'],
    ]);
    $page->defaultFilterId = $filter->id;

    $user->firstPersonalBase()->pivot->updatePreferences(function ($preferences) use ($page, $filter) {
        $preferences->pageSettings[$page->id] = ['defaultFilter' => $filter->id];
    });

    $filter->delete();

    expect($page->fresh())
        ->defaultFilterId->toBeNull();

    expect($page->personalSettings()->first())
        ->settings->defaultFilterId->toBeNull();
});

test('when a field or marker group is deleted from a mapping the saved filter is updated', function () {
    $user = createUser();
    $markerGroupToKeep = createMarkerGroup($user, [], 2);
    $markerGroupToDelete = createMarkerGroup($user, [], 1);
    $categoryToKeep = createCategory();
    $categoryToDelete = createCategory();
    $mapping = createMapping($user, [
        'fields' => [
            ['id' => 'select1', 'name' => 'Select 1', 'type' => FieldType::SELECT(), 'options' => ['valueOptions' => ['A', 'B']]],
            ['id' => 'select2', 'name' => 'Select 2', 'type' => FieldType::SELECT(), 'options' => ['valueOptions' => ['C', 'D']]],
            ['id' => 'category1', 'name' => 'Category 1', 'type' => FieldType::CATEGORY(), 'options' => ['category' => $categoryToDelete->id]],
            ['id' => 'category2', 'name' => 'Category 2', 'type' => FieldType::CATEGORY(), 'options' => ['category' => $categoryToKeep->id]],
        ],
        'markerGroups' => [
            ['id' => 'group1', 'name' => 'Group 1', 'group' => $markerGroupToKeep],
            ['id' => 'group2', 'name' => 'Group 2', 'group' => $markerGroupToDelete],
        ],
    ]);
    $page = createEntitiesPage($mapping);
    $mapping->unsetRelation('pages');
    $filter = $page->savedFilters()->create([
        'name' => 'Test filter',
        'filters' => [
            'fields' => [
                ['fieldId' => 'select1', 'operator' => 'IS', 'match' => '0'],
                ['fieldId' => 'select2', 'operator' => 'IS', 'match' => '0'],
                ['fieldId' => 'select2', 'operator' => 'IS', 'match' => '1'],
                ['fieldId' => 'category1', 'operator' => 'IS', 'match' => json_encode($categoryToDelete->items->first()->global_id)],
                ['fieldId' => 'category2', 'operator' => 'IS', 'match' => json_encode($categoryToKeep->items->first()->global_id)],
                ['fieldId' => 'category2', 'operator' => 'IS', 'match' => json_encode($categoryToKeep->items->last()->global_id)],
            ],
            'markers' => [
                ['markerId' => $markerGroupToKeep->markers->first()->global_id, 'operator' => 'IS', 'context' => 'group1'],
                ['markerId' => $markerGroupToKeep->markers->last()->global_id, 'operator' => 'IS', 'context' => 'group1'],
                ['markerId' => $markerGroupToDelete->markers->first()->global_id, 'operator' => 'IS', 'context' => 'group2'],
            ],
        ],
        'order_by' => [['field' => 'field:category1', 'direction' => 'ASC']],
    ]);
    $markerGroupedFilter = $page->savedFilters()->create([
        'name' => 'Marker group filter',
        'group' => 'marker:group2',
    ]);
    $selectFieldGroupedFilter = $page->savedFilters()->create([
        'name' => 'Marker group filter',
        'group' => 'field:select1',
    ]);
    $categoryFieldGroupedFilter = $page->savedFilters()->create([
        'name' => 'Marker group filter',
        'group' => 'field:category1',
    ]);

    $mapping->removeField('select1');
    $mapping->updateField('select2', ['options' => ['valueOptions' => ['C']]]);
    $markerGroupToDelete->delete();
    $markerGroupToKeep->markers->first()->delete();
    $categoryToDelete->delete();
    $categoryToKeep->items->last()->delete();

    expect($filter->fresh()->filters)
        ->toBe([
            'fields' => [
                ['fieldId' => 'select2', 'operator' => 'IS', 'match' => '0'],
                ['fieldId' => 'category2', 'operator' => 'IS', 'match' => json_encode($categoryToKeep->items->first()->global_id)],
            ],
            'markers' => [
                ['markerId' => $markerGroupToKeep->markers->last()->global_id, 'operator' => 'IS', 'context' => 'group1'],
            ],
        ]);
    expect($filter->fresh()->order_by)->toBeEmpty();
    expect($markerGroupedFilter->fresh()->group)->toBeNull();
    expect($selectFieldGroupedFilter->fresh()->group)->toBeNull();
    expect($categoryFieldGroupedFilter->fresh()->group)->toBeNull();
});
