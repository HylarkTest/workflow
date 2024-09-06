<?php

declare(strict_types=1);

namespace Tests\Actions\Feature;

use Tests\Actions\TestCase;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;

class FeatureTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        config([
            'lighthouse.schema_cache.enable' => false,
            'lighthouse.namespaces.directives' => 'Actions\\GraphQL\\Directives',
        ]);
        $this->app->singleton(SchemaSourceProvider::class, function (): SchemaSourceProvider {
            return new class implements SchemaSourceProvider
            {
                public function setRootPath(string $path): void {}

                public function getSchemaString(): string
                {
                    return /* @lang GraphQL */ <<<'SDL'
"A datetime string with format `Y-m-d H:i:s`, e.g. `2018-01-01 13:00:00`."
scalar DateTime @scalar(class: "LighthouseHelpers\\Scalars\\DateTime")

"A JSON string"
scalar JSON @scalar(class: "LighthouseHelpers\\Scalars\\JSON")

type Query {
    modelWithAction(id: ID!): ModelWithAction @find(model: "Tests\\Actions\\ModelWithAction")
    modelsWithAction: [ModelWithAction!]! @all(model: "Tests\\Actions\\ModelWithAction") @actionFilters
}

type Mutation {
    updateModelWithAction(id: ID!, name: String!): ModelWithAction
       @update(model: "Tests\\Actions\\ModelWithAction")
       @record(model: "Tests\\Actions\\ModelWithAction")
}

interface Performer {
    id: ID!
    actionsPerformed: ActionConnection
}

type Action {
    id: ID! @globalId(type: "Action")
    type: ActionType!
    payload: JSON
    description: String! @method
    changes: [ActionChange!] @method
    createdAt: DateTime!
    updatedAt: DateTime!
    performer: Performer @hasOne
    subject: ActionSubject @hasOne
}

type ActionChange {
    description: String!
    before: String
    after: String
    type: String
}

type ModelWithAction implements ActionSubject @node(model: "Tests\\Actions\\ModelWithAction", type: "ModelWithAction") {
    id: ID!
    name: String!
}
type User implements Performer @node(model: "Illuminate\\Auth\\User", type: "User") {
    name: String!
}
SDL;
                }
            };
        });
    }
}
