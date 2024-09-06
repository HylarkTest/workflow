<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Models\Concerns\HasActions;
use Actions\Models\Contracts\ActionSubject;
use LaravelUtils\Database\Eloquent\Casts\CSV;
use Mappings\Models\Category as BaseCategory;
use App\Models\Concerns\HasBaseScopedRelationships;

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
class Category extends BaseCategory implements ActionSubject
{
    use HasActions;
    use HasBaseScopedRelationships;

    public $fillable = [
        'template_refs',
    ];

    public $casts = [
        'template_refs' => CSV::class,
    ];
}
