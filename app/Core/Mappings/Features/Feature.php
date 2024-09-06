<?php

declare(strict_types=1);

namespace App\Core\Mappings\Features;

use App\Models\Mapping;
use Illuminate\Contracts\Support\Arrayable;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class Feature implements Arrayable, AttributeCollectionItem
{
    public array $options;

    protected MappingFeatureType $val;

    protected array $defaultOptions = [];

    public function __construct(protected Mapping $parent, ?array $options = null)
    {
        if ($this->defaultOptions && ! $options) {
            $this->options = $this->defaultOptions;
        } else {
            $this->options = $options ?: [];
        }
    }

    public function type(): MappingFeatureType
    {
        return $this->val;
    }

    public function toArray()
    {
        $array = [
            'val' => $this->type()->value,
        ];
        if ($this->options) {
            $array['options'] = $this->options;
        }

        return $array;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->type()->value;
    }
}
