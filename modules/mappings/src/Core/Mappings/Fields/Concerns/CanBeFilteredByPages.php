<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;

/**
 * @mixin \Mappings\Core\Mappings\Fields\Types\Field
 */
trait CanBeFilteredByPages
{
    public function cannotRemove(): ?string
    {
        $pages = $this->usedByPages();
        if ($pages->isNotEmpty()) {
            return 'This field is used to filter pages. Please remove it from the pages first. Page(s): "'.$pages->implode('name', '", "').'"';
        }

        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page>
     */
    protected function usedByPages(): Collection
    {
        /** @var \App\Models\Base $base */
        $base = tenant();

        return $base->pages->filter(function (Page $page) {
            return collect($page->fieldFilters)
                ->contains(function ($filter) {
                    return $filter['fieldId'] === $this->id();
                });
        });
    }
}
