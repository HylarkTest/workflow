<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

trait TestsActions
{
    use MakesGraphQLRequests;

    public function assertHasActions(Model $node, array $expectedResponse): void
    {
        $this->graphQL('
        query History($forNode: ID!) {
            history(forNode: $forNode) {
                edges {
                    node {
                        type
                        description(withPerformer: false)
                        changes {
                            description
                            before
                            after
                            type
                        }
                    }
                }
            }
        ', ['forNode' => $node->global_id])->assertSuccessfulGraphQL()
            ->assertJson(['data' => ['history' => ['edges' => $expectedResponse]]]);
    }
}
