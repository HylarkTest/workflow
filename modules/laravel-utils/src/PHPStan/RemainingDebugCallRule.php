<?php

declare(strict_types=1);

namespace LaravelUtils\PHPStan;

use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

class RemainingDebugCallRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Node\Expr\FuncCall) {
            return [];
        }

        if (! \in_array($node->name->parts[0], ['dd', 'dump', 'ray'], true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message("Remaining debug call: {$node->name->parts[0]}()")->build(),
        ];
    }
}
