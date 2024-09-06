<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an item can be added to a relationship', function () {
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $toMapping->relationships = $toMapping->relationships->push([
        'id' => 'children',
        'name' => 'Parent',
        'type' => 'MANY_TO_ONE',
        'to' => $toMapping->getKey(),
        'inverse' => true,
    ]);

    $toMapping->save();

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => ['_v' => 'Parent1']]]);
    /** @var \Mappings\Models\Item $firstChild */
    $firstChild = $toMapping->items()->create(['data' => ['name' => ['_v' => 'Child1']]]);
    /** @var \Mappings\Models\Item $secondChild */
    $secondChild = $toMapping->items()->create(['data' => ['name' => ['_v' => 'Child2']]]);

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                parents {
                    addToChildrenRelationship(input: {
                        itemId: \"$parent->global_id\",
                        ids: [\"{$firstChild->global_id}\", \"{$secondChild->global_id}\"],
                        relationshipId: \"children\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ]);

    $this->be($user)->graphQL("
        {
            items {
                parent(id: \"{$parent->global_id}\") {
                    relations {
                        children(first: 20) {
                            edges {
                                node {
                                    id
                                    data {
                                        name { fieldValue }
                                    }
                                }
                            }
                        }
                    }
                }
                child(id: \"{$firstChild->global_id}\") {
                    relations {
                        parent {
                            node {
                                id
                                data {
                                    name { fieldValue }
                                }
                            }
                        }
                    }
                }
            }
        }
    ")->assertJson(['data' => ['items' => [
        'parent' => ['relations' => ['children' => ['edges' => [
            ['node' => ['id' => $firstChild->globalId(), 'data' => ['name' => ['fieldValue' => 'Child1']]]],
            ['node' => ['id' => $secondChild->globalId(), 'data' => ['name' => ['fieldValue' => 'Child2']]]],
        ]]]],
        'child' => ['relations' => ['parent' => ['node' => [
            'id' => $parent->globalId(), 'data' => ['name' => ['fieldValue' => 'Parent1']],
        ]]]],
    ]]]);
});

test('an item cannot be added to a relationship if it is already there', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $toMapping->relationships = $toMapping->relationships->push([
        'id' => 'children',
        'name' => 'Parent',
        'type' => 'MANY_TO_ONE',
        'to' => $toMapping->getKey(),
        'inverse' => true,
    ]);

    $toMapping->save();

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $firstChild */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
    $relationship = $fromMapping->relationships->first();
    $relationship->add($parent, $child->newCollection([$child]));

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                parents {
                    addToChildrenRelationship(input: {
                        itemId: \"$parent->global_id\",
                        ids: [\"{$child->global_id}\"],
                        relationshipId: \"children\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ])->assertJson([
        'errors' => [[
            'extensions' => [
                'category' => 'validation',
            ],
        ]],
    ]);
});

test('an item can be set on a one relationship', function () {
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
    $fromMapping = createMapping($user, [
        'name' => 'Children',
        'singularName' => 'Child',
        'relationships' => [
            [
                'id' => 'parent',
                'name' => 'Parent',
                'type' => 'MANY_TO_ONE',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $toMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $fromMapping->items()->create(['data' => ['name' => 'Child1']]);

    $this->be($user)->graphQL("
        mutation {
            items {
                children {
                    setParentRelationship(input: { itemId: \"$child->global_id\", relationshipId: \"parent\", id: \"$parent->global_id\" }) {
                        code
                    }
                }
            }
        }
    ")->assertSuccessfulGraphQL();
});

test('an item can be removed from a many relationship', function () {
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    $childrenRelation = $parent->relatedItems($fromMapping->relationships->first());
    $childrenRelation->attach($child);

    expect($childrenRelation->getResults())->toHaveCount(1);

    $this->be($user)->graphQL("
        mutation {
            items {
                parents {
                    removeFromChildrenRelationship(input: {
                        itemId: \"$parent->global_id\",
                        ids: \"{$child->global_id}\",
                        relationshipId: \"children\"
                    }) {
                        code
                    }
                }
            }
        }
    ");

    expect($childrenRelation->getResults())->toBeEmpty();
});

test('an item can be removed from a one relationship', function () {
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Parents', 'singularName' => 'Parent']);
    $fromMapping = createMapping($user, [
        'name' => 'Children',
        'singularName' => 'Child',
        'relationships' => [
            [
                'id' => 'parent',
                'name' => 'Parent',
                'type' => 'MANY_TO_ONE',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $toMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $fromMapping->items()->create(['data' => ['name' => 'Child1']]);

    $parentRelation = $child->relatedItems($fromMapping->relationships->first());
    $parentRelation->attach($child);

    static::assertNotNull($parentRelation->getResults());

    $this->be($user)->graphQL("
        mutation {
            items {
                children {
                    removeParentRelationship(input: {
                        itemId: \"$child->global_id\",
                        relationshipId: \"parent\"
                    }) {
                        code
                    }
                }
            }
        }
    ");

    expect($parentRelation->getResults())->toBeNull();
});

test('adding and removing an item to a relationship creates an action', function () {
    config(['actions.automatic' => true]);
    config(['actions.mandatory_performer' => false]);
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $toMapping->relationships = $toMapping->relationships->push([
        'id' => 'children',
        'name' => 'Parent',
        'type' => 'MANY_TO_ONE',
        'to' => $toMapping->getKey(),
        'inverse' => true,
    ]);

    $toMapping->save();

    /** @var \App\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => ['_v' => 'Parent']]]);
    /** @var \App\Models\Item $firstChild */
    $firstChild = $toMapping->items()->create(['data' => ['name' => ['_v' => 'Child']]]);

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                parents {
                    addToChildrenRelationship(input: {
                        itemId: \"$parent->global_id\",
                        ids: [\"{$firstChild->global_id}\"],
                        relationshipId: \"children\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ]);

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                parents {
                    removeFromChildrenRelationship(input: {
                        itemId: \"$parent->global_id\",
                        ids: [\"{$firstChild->global_id}\"],
                        relationshipId: \"children\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ]);

    expect($parent->actions)->toHaveCount(3);
    expect($firstChild->actions)->toHaveCount(3);

    $parentRemoveAction = $parent->actions->first();
    $childRemoveAction = $firstChild->actions->first();
    $parentAddAction = $parent->actions->get(1);
    $childAddAction = $firstChild->actions->get(1);
    expect($parentAddAction->description(false))->toBe('Added "Child" to relationship "Children" on "Parent".');
    expect($childAddAction->description(false))->toBe('Added "Parent" to relationship "Parent" on "Child".');
    expect($parentRemoveAction->description(false))->toBe('Removed "Child" from relationship "Children" on "Parent".');
    expect($childRemoveAction->description(false))->toBe('Removed "Parent" from relationship "Parent" on "Child".');
});

test('setting and removing a relationship creates an action', function () {
    config(['actions.automatic' => true]);
    config(['actions.mandatory_performer' => false]);
    $user = createUser();
    $toMapping = createMapping($user, ['name' => 'Brothers', 'singularName' => 'Brother']);
    $fromMapping = createMapping($user, [
        'name' => 'Sisters',
        'singularName' => 'Sister',
        'relationships' => [
            [
                'id' => 'sibling',
                'name' => 'Sibling',
                'type' => 'ONE_TO_ONE',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $toMapping->relationships = $toMapping->relationships->push([
        'id' => 'sibling',
        'name' => 'Sibling',
        'type' => 'ONE_TO_ONE',
        'to' => $toMapping->getKey(),
        'inverse' => true,
    ]);

    $toMapping->save();

    /** @var \App\Models\Item $sister */
    $sister = $fromMapping->items()->create(['data' => ['name' => ['_v' => 'Sister']]]);
    /** @var \App\Models\Item $brother */
    $brother = $toMapping->items()->create(['data' => ['name' => ['_v' => 'Brother']]]);

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                sisters {
                    setSiblingRelationship(input: {
                        itemId: \"$sister->global_id\",
                        id: \"$brother->global_id\",
                        relationshipId: \"sibling\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ]);

    $this->be($user)->postJson(route('graphql'), [
        'query' => "
        mutation {
            items {
                sisters {
                    removeSiblingRelationship(input: {
                        itemId: \"$sister->global_id\",
                        relationshipId: \"sibling\"
                    }) {
                        code
                    }
                }
            }
        }
        ",
    ]);

    expect($sister->actions)->toHaveCount(3);
    expect($brother->actions)->toHaveCount(3);

    $sisterRemoveAction = $sister->actions->first();
    $brotherRemoveAction = $brother->actions->first();
    $sisterSetAction = $sister->actions->get(1);
    $brotherSetAction = $brother->actions->get(1);
    expect($sisterSetAction->description(false))->toBe('Added "Brother" to relationship "Sibling" on "Sister".');
    expect($brotherSetAction->description(false))->toBe('Added "Sister" to relationship "Sibling" on "Brother".');
    expect($sisterRemoveAction->description(false))->toBe('Removed "Brother" from relationship "Sibling" on "Sister".');
    expect($brotherRemoveAction->description(false))->toBe('Removed "Sister" from relationship "Sibling" on "Brother".');
});

test('a relationship can be made to the same mapping', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'singularName' => 'Person']);

    $this->be($user)->assertGraphQLMutation(
        'createMappingRelationship(input: $input)',
        ['input: CreateMappingRelationshipInput!' => [
            'mappingId' => $mapping->global_id,
            'name' => 'Children',
            'type' => 'ONE_TO_MANY',
            'to' => $mapping->global_id,
            'inverseName' => 'Parent',
        ]]
    );

    tenancy()->initialize($user->firstPersonalBase());

    /** @var \Mappings\Models\Item $parent */
    $parent = $mapping->items()->create(['data' => ['name' => ['_v' => 'Parent1']]]);
    /** @var \Mappings\Models\Item $firstChild */
    $child = $mapping->items()->create(['data' => ['name' => ['_v' => 'Child1']]]);

    $relationship = $mapping->fresh()->relationships->first();

    $this->be($user)->assertGraphQLMutation(
        'items.people.addToChildrenRelationship(input: $input).code',
        ['input: AddManyRelationshipsInput!' => [
            'itemId' => $parent->global_id,
            'ids' => [$child->global_id],
            'relationshipId' => $relationship->id(),
        ]]
    );

    $this->be($user)->assertGraphQL(['items' => [
        "children: person(id: \"$parent->global_id\")" => ['relations' => ['children(first:20)' => ['edges' => [[
            'node' => ['id' => $child->global_id, 'data' => ['name' => ['fieldValue' => 'Child1']]],
        ]]]]],
        "parent: person(id: \"$child->global_id\")" => ['relations' => ['parent' => [
            'node' => ['id' => $parent->global_id, 'data' => ['name' => ['fieldValue' => 'Parent1']]],
        ]]],
    ]]);
});
