<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Actions\RestoreVersion;
use App\Models\Support\ArticleStatus;
use Hylark\ArticleContent\ArticleContent;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Support\SupportArticle as SupportArticleModel;

/**
 * @mixin \App\Models\Support\SupportArticle
 *
 * @extends \App\Nova\Resource<\App\Models\Support\SupportArticle>
 */
class SupportArticleVersion extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SupportArticleModel::class;

    /**
     * The single value that should be used to represent
     * the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    public static $searchable = false;

    public static $tableStyle = 'tight';

    public static $perPageViaRelationship = 10;

    public static function label()
    {
        return 'Versions';
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToRunAction(NovaRequest $request, Action $action)
    {
        return (bool) $request->user()?->can('update', $this->resource);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Edited by'),

            Badge::make('Status', fn (SupportArticleModel $article) => $this->live_at ? "{$article->status->value} (live)" : $article->status->value)
                ->map([
                    ArticleStatus::PUBLISHED->value => 'success',
                    ArticleStatus::PUBLISHED->value.' (live)' => 'success',
                    ArticleStatus::DRAFT->value => 'info',
                ]),

            DateTime::make('Created', 'updated_at'),

            ArticleContent::make('Content')
                ->stacked()
                ->fullWidth()
                ->alwaysShow()
                /** @phpstan-ignore-next-line It will always be a model */
                ->fillUsing(function (NovaRequest $request, SupportArticleModel $model, string $attribute, string $requestAttribute) {
                    $model->content = clean($request[$requestAttribute]);

                    return $model->content;
                }),
        ];
    }

    /**
     *  Get the actions available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function actions(NovaRequest $request): array
    {
        return [
            (new RestoreVersion)->sole(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->withoutGlobalScope('published');
    }
}
