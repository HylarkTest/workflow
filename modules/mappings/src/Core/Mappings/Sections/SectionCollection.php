<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Sections;

use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Database\Eloquent\AttributeCollection;

/**
 * @extends \LaravelUtils\Database\Eloquent\AttributeCollection<int, \Mappings\Core\Mappings\Sections\Section>
 *
 * @phpstan-import-type SectionOptions from \Mappings\Core\Mappings\Sections\Section
 */
class SectionCollection extends AttributeCollection
{
    /**
     * @param  array<int, SectionOptions>|\Mappings\Core\Mappings\Sections\SectionCollection  $items
     */
    public static function makeFromAttribute($items, Model $model): self
    {
        if ($items instanceof self) {
            return $items;
        }

        $sections = [];

        foreach ((array) $items as $item) {
            $sections[] = new Section($item);
        }

        return new self($sections);
    }

    /**
     * @param  SectionOptions  $args
     */
    public function addItem(array $args, Model $model): Section
    {
        $this->push($section = new Section($args));

        return $section;
    }

    /**
     * @param  int|string  $id
     * @param  SectionOptions  $args
     */
    public function changeItem($id, array $args, Model $model): ?Section
    {
        /** @var \Mappings\Core\Mappings\Sections\Section|null $section */
        $section = $this->find($id);

        if ($section) {
            $section->name = $args['name'];
        }

        return $section;
    }

    /**
     * @param  int|string  $id
     */
    public function removeItem($id, Model $model): ?Section
    {
        return $this->forgetItem($id);
    }
}
