<?php

declare(strict_types=1);

namespace Finder\Core;

use Illuminate\Database\Eloquent\Model;

interface FinderKeyResolverInterface
{
    public function generateKey(Model $model, string $index): string;

    /**
     * @return array{0: class-string<\Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>, 1: int|string}
     */
    public function extractClassAndIdFromKey(string $key, string $index): array;
}
