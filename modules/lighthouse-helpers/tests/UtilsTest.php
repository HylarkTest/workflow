<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use LighthouseHelpers\Utils;

class UtilsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Utils can generate a graphql safe random string
     *
     * @test
     */
    public function utils_can_generate_a_graphql_safe_random_string(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $string = Utils::generateRandomString();
            $this->assertGraphQLCompatible($string);
        }
    }

    /**
     * Utils can change a string to a graphql safe type
     *
     * @test
     */
    public function utils_can_change_a_string_to_a_graphql_safe_type(): void
    {
        $this->assertGraphQLCompatible(Utils::generateGraphQLType('123abc'));
        $this->assertGraphQLCompatible(Utils::generateGraphQLType('a.b.c'));
        $this->assertGraphQLCompatible(Utils::generateGraphQLType('cuţit')); // cspell:disable-line
    }

    protected function assertGraphQLCompatible($string): void
    {
        static::assertMatchesRegularExpression('/^[a-zA-Z_]\w*$/', $string);
    }
}
