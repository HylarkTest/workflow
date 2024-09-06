<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\HasManyThrough;
use App\Nova\Repeater\SupportFolderItem;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

/**
 * @mixin \App\Models\Support\SupportCategory
 *
 * @extends \App\Nova\Resource<\App\Models\Support\SupportCategory>
 */
class SupportCategory extends Resource
{
    use HasSortableRows;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Support\SupportCategory::class;

    public static $searchable = false;

    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)
            ->withoutGlobalScope('public');
    }

    public function title()
    {
        return $this->name;
    }

    public static function label()
    {
        return 'Categories';
    }

    public function authorizedToDelete(Request $request)
    {
        return $this->articles()->doesntExist();
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
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
                ->sortable()
                ->rules('required', 'max:1000'),

            Boolean::make('Public', 'is_public')
                ->default(true),

            Repeater::make('Folders')
                ->sortable(false)
                ->repeatables([SupportFolderItem::make()])
                ->uniqueField('name')
                ->asHasMany(SupportFolder::class),

            Number::make('Article count', function () {
                return $this->articles()->count();
            })->exceptOnForms(),

            HasMany::make('Folders', 'folders', SupportFolder::class),
            HasManyThrough::make('Articles', 'allArticles', SupportArticle::class),
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
