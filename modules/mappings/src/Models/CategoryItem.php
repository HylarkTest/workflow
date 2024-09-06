<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Class Category
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Mappings\Models\Category $category
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Mappings\Models\CategoryItem>
 */
class CategoryItem extends Model implements Sortable
{
    use HasGlobalId;
    use IsSortable;

    protected $casts = [
        'category_id' => 'int',
    ];

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Mappings\Models\Category, \Mappings\Models\CategoryItem>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(config('mappings.models.category'));
    }
}
