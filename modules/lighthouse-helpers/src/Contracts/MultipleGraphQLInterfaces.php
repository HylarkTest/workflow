<?php

declare(strict_types=1);

namespace LighthouseHelpers\Contracts;

interface MultipleGraphQLInterfaces
{
    public static function resolveType(self $model): string;
}
