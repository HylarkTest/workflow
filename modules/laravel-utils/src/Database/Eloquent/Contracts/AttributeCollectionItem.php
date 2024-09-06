<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @extends \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
interface AttributeCollectionItem extends \JsonSerializable, Arrayable
{
    public function id();
}
