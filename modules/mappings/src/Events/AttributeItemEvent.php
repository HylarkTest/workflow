<?php

declare(strict_types=1);

namespace Mappings\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

abstract class AttributeItemEvent
{
    use SerializesModels;

    public string $attribute;

    public AttributeCollectionItem $item;

    public Model $model;

    public function __construct(Model $model, string $attribute, AttributeCollectionItem $item)
    {
        $this->model = $model;
        $this->attribute = $attribute;
        $this->item = $item;
    }
}
