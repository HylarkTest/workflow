<?php

declare(strict_types=1);

namespace LighthouseHelpers;

use GraphQL\Language\Parser;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\ManipulateAST;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class PostLighthouseServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(
            RegisterDirectiveNamespaces::class, fn () => 'LighthouseHelpers\\Directives'
        );

        $events->listen(
            ManipulateAST::class,
            function (ManipulateAST $manipulateAST): void {
                $documentAST = $manipulateAST->documentAST;
                $documentAST->setTypeDefinition(
                    Parser::inputObjectTypeDefinition(/* @lang GraphQL */ <<<'GRAPHQL'
                        "Allows ordering a list of records."
                        input OrderByClause {
                            "The column that is used for ordering."
                            field: String!

                            "The direction that is used for ordering."
                            direction: SortOrder!
                        }
GRAPHQL
                    )
                );
            }
        );
    }
}
