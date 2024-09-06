<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

class MutationValidationTest extends TestCase
{
    /**
     * Validation can be performed inside a mutation
     *
     * @test
     */
    public function validation_can_be_performed_inside_a_mutation(): void
    {
        $this->handleGraphQLExceptions();
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
            type Query
            type Mutation {
                testMutation: Int
            }
        SDL
        );

        $this->graphQL('mutation { testMutation }')
            ->assertJson(['errors' => [['extensions' => ['validation' => [
                'name' => ['The name field is required.'],
            ]]]]]);
    }
}

namespace App\GraphQL\Mutations;

use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class TestMutation extends Mutation
{
    /**
     * @param  mixed  $root
     * @param  mixed  $args
     *
     * @throws \Exception
     */
    public function __invoke($root, $args, GraphQLContext $context, ResolveInfo $resolveInfo): int
    {
        $this->validate($args, ['name' => 'required'], $resolveInfo);

        return 1;
    }
}
