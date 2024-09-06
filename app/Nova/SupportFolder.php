<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

/**
 * @mixin \App\Models\Support\SupportFolder
 *
 * @extends \App\Nova\Resource<\App\Models\Support\SupportFolder>
 */
class SupportFolder extends Resource
{
    use HasSortableRows;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Support\SupportFolder::class;

    public static $tableStyle = 'tight';

    public static $with = ['category'];

    public static $searchable = false;

    public static function label()
    {
        return 'Folders';
    }

    public function title()
    {
        return $this->name." ({$this->category->name})";
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return $this->articles()->doesntExist();
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required', 'max:1000'),

            BelongsTo::make('Category', 'category', SupportCategory::class)
                ->dontReorderAssociatables()->sortable(),

            Number::make('Article count', function () {
                return $this->articles()->count();
            })->readonly(),

            HasMany::make('Articles', 'allArticles', SupportArticle::class),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     *  Get the actions available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
