<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Support\Utils;
use Nuwave\Lighthouse\Schema\Directives\BuilderDirective as BaseBuilderDirective;

class BuilderDirective extends BaseBuilderDirective
{
    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function getResolverFromArgument(string $argumentName): \Closure
    {
        [$className, $methodName] = $this->getMethodArgumentParts($argumentName);

        $namespacesToTry = $argumentName === 'method' ? config('lighthouse.namespaces.builders') : [];

        $namespacedClassName = $this->namespaceClassName($className, (array) $namespacesToTry);

        return Utils::constructResolver($namespacedClassName, $methodName);
    }
}
