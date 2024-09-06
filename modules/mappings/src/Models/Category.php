<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Category
 *
 * @property int $id
 * @property string[]|null $template_refs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<\Mappings\Models\Item> $items
 */
class Category extends Model
{
    use HasGlobalId;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'description',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Mappings\Models\CategoryItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(config('mappings.models.category_item'));
    }
}
