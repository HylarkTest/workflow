<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class StringArgsDirectiveTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * Fields using the stringArgs directive can be truncated
     *
     * @test
     */
    public function fields_using_the_string_args_directive_can_be_truncated(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
scalar JSON @scalar(class: "LighthouseHelpers\\Scalars\\JSON")

type Query {
    field: String @stringArgs @field(resolver: "Tests\\LighthouseHelpers\\TestResolver")
}
SDL
        );

        $this->graphQL('{
            field
            truncatedField: field(truncate: 3)
            truncatedFieldWithSuffix: field(truncate: 3, suffix: "...etc.")
            trimmedField: field(truncate: 5)
        }')->assertJson(['data' => [
            'field' => 'Test string value',
            'truncatedField' => 'Tes...',
            'truncatedFieldWithSuffix' => 'Tes...etc.',
            'trimmedField' => 'Test ...',
        ]]);
    }
}

class TestResolver
{
    public function __invoke(): string
    {
        return 'Test string value';
    }
}
