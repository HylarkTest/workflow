<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Mapping;
use Illuminate\Testing\TestResponse;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Database\Eloquent\Collection;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\LighthouseHelpers\Concerns\GeneratesFileRequests;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

/**
 * @mixin TestCase
 */
trait TestsFields
{
    use GeneratesFileRequests;
    use InteractsWithGraphQLExceptionHandling;
    use MakesGraphQLRequests;
    use RefreshDatabase;
    use UsesElasticsearch;

    public function assertItemUpdateCreatedActions(
        FieldType $type,
        array $options = [],
        $original = null,
        $value = null,
        ?array $createChange = null,
        ?array $updateChange = null,
        ?\Closure $beforeUpdateCb = null
    ): void {
        enableAllActions();

        $user = auth()->user() ?? createUser();
        $mapping = $this->createMappingWithField($user, $type, $options, 'field');

        $item = createItem($mapping, ['fieldId' => $this->formatExpectedValue($original)]);
        $item->wasRecentlyCreated = false;

        $beforeUpdateCb && $beforeUpdateCb($item, $mapping);

        $item->update(['data' => ['fieldId' => $this->formatExpectedValue($value)]]);

        $actions = $item->actions;
        expect($actions)->toHaveCount(2);

        /** @var \App\Models\Action $createAction */
        $createAction = $actions->last();
        $updateAction = $actions->first();
        expect($createAction)
            ->description(false)->toBe('Item "Item" created')
            ->changes()->toBe([[
                'description' => 'Added the "field"',
                'before' => null,
                'after' => $createChange['after'] ?? $original['fieldValue'],
                'type' => $createChange['type'] ?? 'line',
            ]])
            ->and($updateAction)
            ->description(false)->toBe('Item "Item" updated')
            ->changes()->toBe([[
                'description' => 'Changed the "field"',
                'before' => $updateChange['before'] ?? $original['fieldValue'],
                'after' => $updateChange['after'] ?? $value['fieldValue'],
                'type' => $updateChange['type'] ?? 'line',
            ]]);
    }

    public function assertFieldCreated(FieldType $type, array $options = []): void
    {
        $user = createUser();
        $mapping = createMapping($user);

        $this->be($user)->sendAddFieldRequest($mapping, $type, $options)
            ->assertSuccessfulGraphQL();

        /** @var \Mappings\Core\Mappings\Fields\Field $field */
        $field = $mapping->fresh()->fields->last();
        $this->assertEquals('field', $field->name);
        $this->assertTrue($field->type()->is($type));
        if ($options) {
            $this->assertEquals($options, $field->options());
        } else {
            $this->assertEmpty($field->options());
        }
    }

    public function assertInvalidAddFieldRequest(FieldType $type, array $options, $expectedErrors): TestResponse
    {
        $this->withGraphQLExceptionHandling();
        $user = createUser();
        $mapping = createMapping($user);

        return $this->be($user)->sendAddFieldRequest($mapping, $type, $options)
            ->assertJson(['errors' => [['extensions' => ['validation' => $expectedErrors]]]]);
    }

    public function sendAddFieldRequest(Mapping $mapping, FieldType $type, $options = []): TestResponse
    {
        return $this->graphQL('
            mutation($input: CreateMappingFieldInput!) {
                createMappingField(input: $input) {
                    code
                    mapping { id }
                }
            }
            ',
            [
                'input' => [
                    'mappingId' => $mapping->globalId(),
                    'name' => 'field',
                    'type' => $type->key,
                    'options' => $options,
                ],
            ],
        );
    }

    public function sendUpdateFieldRequest(Mapping $mapping, string $fieldId, array $options = [], bool $succeeded = true): TestResponse
    {
        return $this->assertGraphQL(
            'updateMappingField(input: $input)',
            ['input: UpdateMappingFieldInput!' => [
                'mappingId' => $mapping->globalId(),
                'id' => $fieldId,
                'options' => $options,
            ]],
            'mutation',
            $succeeded,
        );
    }

    public function sendDeleteFieldRequest(Mapping $mapping, string $fieldId, bool $succeeded = true): TestResponse
    {
        return $this->assertGraphQL(
            'deleteMappingField(input: $input)',
            ['input: DeleteMappingFieldInput!' => [
                'mappingId' => $mapping->globalId(),
                'id' => $fieldId,
            ]],
            'mutation',
            $succeeded,
        );
    }

    public function assertItemCreatedWithField(
        FieldType $type,
        array $options,
        $requestBody,
        $expectedResponse = null,
        $expectedValue = null,
        $request = null
    ): void {
        $expectedResponse = $expectedResponse ?: $requestBody;

        $expectedValue = $expectedValue ?: $expectedResponse;

        $user = auth()->user() ?? createUser();
        $mapping = $this->createMappingWithField($user, $type, $options, 'field');

        if (! $request) {
            $request = ($options['list'] ?? false) ? 'field { listValue { fieldValue } }' : 'field { fieldValue }';
        }

        $this->be($user)->sendCreateItemRequest($mapping, ['field' => $requestBody], $request)->assertJson(['data' => ['items' => [
            $mapping->graphql_many_field => [
                'createItem' => [
                    'code' => '200',
                    $mapping->graphql_single_field => [
                        'data' => [
                            'field' => value($expectedResponse),
                        ],
                    ],
                ],
            ],
        ]]], true);

        /** @var \Mappings\Models\Item $item */
        $item = $mapping->items()->first();

        $this->assertEquals($this->formatExpectedValue(value($expectedValue)), $item->data['fieldId']);
    }

    public function assertItemUpdatedWithField(
        FieldType $type,
        array $options,
        $original,
        $requestBody,
        $expectedResponse = null,
        $expectedValue = null,
        $request = null
    ): void {
        $expectedResponse = $expectedResponse ?: $requestBody;
        $expectedValue = $expectedValue ?: $expectedResponse;

        $user = auth()->user() ?? createUser();
        $mapping = $this->createMappingWithField($user, $type, $options, 'field');

        if (! $request) {
            $request = ($options['list'] ?? false) ? 'field { listValue { fieldValue } }' : 'field { fieldValue }';
        }

        $item = createItem($mapping, ['fieldId' => $this->formatExpectedValue($original)]);

        $this->be($user)->sendUpdateItemRequest($item, $mapping, ['field' => $requestBody], $request)->assertJson(['data' => ['items' => [
            $mapping->graphql_many_field => [
                'updateItem' => [
                    'code' => '200',
                    $mapping->graphql_single_field => [
                        'data' => [
                            'field' => value($expectedResponse),
                        ],
                    ],
                ],
            ],
        ]]], true);

        /** @var \Mappings\Models\Item $item */
        $item = $mapping->items()->first();

        $this->assertEquals($this->formatExpectedValue(value($expectedValue)), $item->data['fieldId'] ?? null);
    }

    public function assertValidFieldRequest(FieldType $type, array $options, $body, $expectedResponse, $request = null): TestResponse
    {
        $user = auth()->user() ?: createUser();
        $mapping = $this->createMappingWithField($user, $type, $options);

        return $this->be($user)->sendCreateItemRequest($mapping, $body, $request)
            ->assertJson(['data' => ['items' => [
                $mapping->graphql_many_field => [
                    'createItem' => [
                        'code' => '200',
                        $mapping->graphql_single_field => [
                            'data' => value($expectedResponse),
                        ],
                    ],
                ],
            ]]], true);
    }

    public function assertFieldIsSortable(FieldType $type, array $options, array $fieldValues, array $expectedDescOrder = [1, 2, 0], ?array $expectedAscOrder = null): void
    {
        if (! $expectedAscOrder) {
            $expectedAscOrder = array_reverse($expectedDescOrder);
        }
        $user = createUser();
        $mapping = $this->createMappingWithField($user, $type, $options);

        $items = $this->createSortableItems($mapping, $fieldValues);

        $this->be($user)->assertGraphQL(
            ['items' => [
                'items(orderBy: [{ field: "field:fieldId", direction: DESC }])' => [
                    'edges' => array_map(
                        fn ($index) => ['node' => ['id' => $items[$index]->globalId()]],
                        $expectedDescOrder
                    ),
                ],
            ]],
        );
        $this->be($user)->assertGraphQL(
            ['items' => [
                'items(orderBy: [{ field: "field:fieldId", direction: ASC }])' => [
                    'edges' => array_map(
                        fn ($index) => ['node' => ['id' => $items[$index]->globalId()]],
                        $expectedAscOrder
                    ),
                ],
            ]],
        );
    }

    public function assertFieldIsNotSortable(FieldType $type, array $options = [])
    {
        $this->withGraphQLExceptionHandling();
        $user = createUser();
        $this->createMappingWithField($user, $type, $options);

        $this->be($user)->assertGraphQL(
            'items.items(orderBy: [{ field: "field:fieldId", direction: DESC }]).edges.node.id',
            [],
            'query',
            false,
        )->assertGraphQLValidationError('orderBy', 'Cannot sort by the selected field.');
    }

    public function assertInvalidFieldRequest(FieldType $type, array $options, $body, $expectedErrors, $request = null): TestResponse
    {
        $this->withGraphQLExceptionHandling();
        $user = auth()->user() ?? createUser();
        $mapping = $this->createMappingWithField($user, $type, $options);

        return $this->be($user)->sendCreateItemRequest($mapping, $body, $request)
            ->assertJson(['errors' => [['extensions' => ['validation' => $expectedErrors]]]]);
    }

    /**
     * @param  null  $request
     * @param  mixed  $body
     */
    public function sendCreateItemRequest(Mapping $mapping, $body, $request = null): TestResponse
    {
        $type = $mapping->graphql_type;
        $singleField = $mapping->graphql_single_field;
        $request = $request ?: $mapping->fields->last()->apiName.'{ fieldValue }';

        return $this->convertToFileRequest(route('graphql', $mapping->globalId()), [
            'query' => "
            mutation(\$item: {$type}ItemDataInput) {
                items {
                    items {
                        create{$type}(input: { data: \$item }) {
                            code
                            {$singleField} {
                                data {
                                    $request
                                }
                            }
                        }
                    }
                }
            }
            ",
            'variables' => [
                'item' => array_merge(['name' => ['fieldValue' => 'Larray']], $body),
            ],
        ]);
    }

    /**
     * @param  mixed  $body
     */
    public function sendUpdateItemRequest(Item $item, Mapping $mapping, $body, ?string $request = null): TestResponse
    {
        $type = $mapping->graphql_type;
        $singleField = $mapping->graphql_single_field;
        $request = $request ?: $mapping->fields->last()->apiName;

        return $this->convertToFileRequest(route('graphql', $mapping->globalId()), [
            'query' => "
            mutation(\$id: ID!, \$item: {$type}ItemDataInput) {
                items {
                    $mapping->graphql_many_field {
                        update{$type}(input: { id: \$id, data: \$item }) {
                            code
                            $singleField {
                                data {
                                    $request
                                }
                            }
                        }
                    }
                }
            }
            ",
            'variables' => [
                'id' => $item->globalId(),
                'item' => $body,
            ],
        ]);
    }

    public function fetchItemRequest(FieldType $type, $options, $value, $request, $expectedResponse): TestResponse
    {
        $user = createUser();
        $mapping = $this->createMappingWithField($user, $type, $options);

        $item = createItem($mapping, ['fieldId' => $this->formatExpectedValue($value)]);
        $id = $item->globalId();

        return $this->be($user)->postJson(route('graphql', $mapping->globalId()), [
            'query' => "
            {
                items {
                    item(id: \"$id\") {
                        $request
                    }
                }
            }
            ",
        ])->assertJson(['data' => ['items' => [
            'item' => $expectedResponse,
        ]]], true);
    }

    /**
     * @param  mixed  $value
     * @return mixed|mixed[]
     *                       Convert a response from the user facing structure to the more compact structure stored in the DB
     */
    protected function formatExpectedValue($value)
    {
        if (! \is_array($value)) {
            return $value;
        }
        foreach ([
            'listValue' => Field::LIST_VALUE,
            'fieldValue' => Field::VALUE,
            'label' => Field::LABEL,
            'main' => Field::IS_MAIN,
        ] as $responseKey => $dataKey) {
            if (\array_key_exists($responseKey, $value)) {
                $value[$dataKey] = $value[$responseKey];
                unset($value[$responseKey]);
            }
        }

        return collect($value)->mapWithKeys(function ($value, $key) {
            return [$key => $this->formatExpectedValue($value)];
        })->toArray();
    }

    protected function createMappingWithField(User $user, FieldType $type, array $options = [], $name = 'field'): Mapping
    {
        return createMapping($user, [
            'name' => 'items',
            'singularName' => 'item',
            'fields' => [
                [
                    'id' => 'name',
                    'name' => 'Name',
                    'type' => FieldType::SYSTEM_NAME()->key,
                ],
                [
                    'id' => $name.'Id',
                    'name' => $name,
                    'type' => $type->key,
                    'options' => $options,
                ],
            ],
        ]);
    }

    protected function createSortableItems(Mapping $mapping, array $fieldValues): Collection
    {
        return (new Item)->newCollection(
            collect($fieldValues)->map(fn ($value) => createItem(
                $mapping,
                $value === null
                    ? []
                    : ['fieldId' => (isset($value['_v']) ? $value : ['_v' => $value])]
            ))->all()
        );
    }
}
