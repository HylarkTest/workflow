<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Color\Color;
use App\Models\Space;
use Finder\CanBeGloballySearched;
use Actions\Models\Concerns\HasActions;
use Illuminate\Database\Eloquent\Builder;
use LighthouseHelpers\Concerns\HasGlobalId;
use LaravelUtils\Database\Eloquent\Casts\CSV;
use LaravelUtils\Database\Eloquent\ColorCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

/**
 * @template TItem of \App\Models\Contracts\FeatureListItem
 * @template TList of \App\Models\Contracts\FeatureList
 *
 * @implements \App\Models\Contracts\FeatureList<TItem, TList>
 */
trait HasFeatureListMethods
{
    use AdvancedSoftDeletes{
        getDeleteCascadeRelationships as traitGetDeleteCascadeRelationships;
    }
    use CanBeGloballySearched;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasBaseScopedRelationships;
    use HasFactory;
    use HasGlobalId;
    use HasMarkers;
    use IsSortable;

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'is_default' => 'boolean',
            'color' => ColorCast::class,
            'template_refs' => CSV::class,
        ]);
    }

    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'space_id',
            'name',
            'is_default',
            'template_refs',
            'color',
        ]);
    }

    /**
     * @return array{primary: string|array<string>, secondary?: string|array<string>}
     */
    public function toGloballySearchableArray(): array
    {
        return [
            'id' => $this->id,
            'space_id' => $this->space_id,
            'primary' => [
                'text' => $this->name,
                'map' => 'name',
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Space, TList>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<TList>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('space_id', $this->getAttribute('space_id'));
    }

    public function colorOrDefault(): Color
    {
        $color = $this->color;
        if (! $color) {
            $defaultHex = config('planner.events.default_color', '#AEAEAE');

            return Color::make($defaultHex);
        }

        return $color;
    }

    public function getActionIgnoredColumns(): array
    {
        return ['is_default'];
    }

    public static function formatOrderActionPayload(): null
    {
        return null;
    }

    protected function getDeleteCascadeRelationships(): array
    {
        return array_merge([
            'children',
        ], $this->traitGetDeleteCascadeRelationships());
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootHasFeatureListMethods()
    {
        self::updated(function (self $list) {
            if ($list->wasChanged('name')) {
                /** @phpstan-ignore-next-line `globallySearchable` exists */
                $list->children()->globallySearchable();
            }
        });
    }
}
