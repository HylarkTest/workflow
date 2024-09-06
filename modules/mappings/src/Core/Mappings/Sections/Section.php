<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Sections;

use LighthouseHelpers\Utils;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @phpstan-type SectionOptions = array{
 *     id?: string,
 *     name: string,
 * }
 */
class Section implements AttributeCollectionItem
{
    public const MAX_LENGTH = 50;

    public string $id;

    public string $name;

    /**
     * @param  SectionOptions  $section
     */
    public function __construct(array $section)
    {
        $this->id = $section['id'] ?? Utils::generateRandomString();
        $this->name = $section['name'];
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    public function toArray()
    {
        return [
            'id' => $this->id(),
            'name' => $this->name,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
