<?php

declare(strict_types=1);

namespace Finder\Core;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TModel of \Finder\GloballySearchable
 *
 * @extends \Illuminate\Support\Collection<TKey, TModel>
 */
class FinderCollection extends Collection {}
