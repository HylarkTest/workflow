<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives\Concerns;

use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\GlobalId\GlobalIdDirective;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

trait UsesGlobalIdArg
{
    protected function getGlobalIdValues(ResolveInfo $resolveInfo): ?Argument
    {
        return Arr::first(
            /** @phpstan-ignore-next-line Deprecated in 8.2 but not much to be done until Lighthouse upgrades */
            $resolveInfo->argumentSet->arguments,
            static function (Argument $arg) {
                return $arg->directives->contains(
                    fn (Directive $directive) => $directive instanceof GlobalIdDirective
                );
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|null
     *
     * @throws \Throwable
     */
    protected function globalIdBuilder(ResolveInfo $resolveInfo): ?Builder
    {
        $idArg = $this->getGlobalIdValues($resolveInfo);
        if ($idArg && \is_array($idArg->value)) {
            [$type, $id] = $idArg->value;

            $class = Utils::namespaceModelClass($type);

            throw_if(! $class, GlobalIdException::class, 'Global ID invalid');

            return $class::query()->whereKey($id);
        }

        return null;
    }
}
