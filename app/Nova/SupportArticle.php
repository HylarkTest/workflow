<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Actions\PublishArticle;
use App\Models\Support\ArticleStatus;
use Illuminate\Database\Eloquent\Model;
use Hylark\ArticleContent\ArticleContent;
use Illuminate\Database\Eloquent\Builder;
use Pavloniym\ActionButtons\ActionButton;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;
use App\Models\Support\SupportArticle as SupportArticleModel;

/**
 * @mixin \App\Models\Support\SupportArticle
 *
 * @extends \App\Nova\Resource<\App\Models\Support\SupportArticle>
 */
class SupportArticle extends Resource
{
    use HasSortableRows {
        indexQuery as traitIndexQuery;
    }

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

    public static $with = [
        'folder',
        'topics',
        'versions:id,latest_id,status,live_at',
    ];

    public static function label()
    {
        return 'Articles';
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function fieldsForDetail(NovaRequest $request): array
    {
        $statusButton = ActionButton::make('')
            ->action(new PublishArticle, $this->id)
            ->text('Publish')
            ->classes(['bg-green-500', 'hover:bg-green-700'])
            ->canSeeWhen('update', $this)
            ->textAlign('right');

        return [
            ...($this->status === ArticleStatus::DRAFT ? [$statusButton] : []),
            Badge::make('Status', fn (SupportArticleModel $article) => $this->live_at ? "{$article->status->value} (live)" : $article->status->value)
                ->map([
                    ArticleStatus::PUBLISHED->value => 'success',
                    ArticleStatus::PUBLISHED->value.' (live)' => 'success',
                    ArticleStatus::DRAFT->value => 'info',
                ]),
            Text::make('Title'),
            Text::make('Edited by'),
            Text::make('Friendly URL'),
            /** @phpstan-ignore-next-line It's what the docs say to do */
            Tag::make('Topics', 'topics', SupportTopic::class),
            Text::make('Stats', function () {
                return <<<HTML
<table class="min-w-full border-collapse">
    <tr>
        <td class="border p-2 text-sm font-semibold text-blue-600">📈 Views: <span id="viewCount">{$this->views}</span></td>
        <td class="border p-2 text-sm font-semibold text-green-600">👍 <span id="thumbsUpCount">{$this->thumbs_up}</span></td>
        <td class="border p-2 text-sm font-semibold text-red-600">👎 <span id="thumbsDownCount">{$this->thumbs_down}</span></td>
    </tr>
</table>
HTML;
            })->asHtml(),
            DateTime::make('Created', 'created_at'),

            ArticleContent::make('Content')
                ->stacked()
                ->fullWidth()
                ->alwaysShow()
                /** @phpstan-ignore-next-line It will always be a model */
                ->fillUsing(function (NovaRequest $request, SupportArticleModel $model, string $attribute, string $requestAttribute) {
                    $model->content = clean($request[$requestAttribute], 'youtube');

                    return $model->content;
                }),

            HasMany::make('Versions', 'versions', SupportArticleVersion::class),

            Panel::make('Linked Articles', [
                Stack::make('Links in this article', $this->getArticlesLinkedTo()->map(function (SupportArticleModel $article) {
                    return Line::make('', fn () => "<a href=\"/nova/resources/support-articles/$article->id\">$article->title</a>")->asHtml();
                })->all()),
                Stack::make('Articles linking to this article', $this->liveLinkedArticles()->map(function (SupportArticleModel $article) {
                    return Line::make('', fn () => "<a href=\"/nova/resources/support-articles/$article->id\">$article->title</a>")->asHtml();
                })->all()),
            ]),
        ];
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Title')->rules('required', 'max:1000'),
            BelongsTo::make('Folder', 'folder', SupportFolder::class),
            Text::make('Friendly URL', 'friendly_url')->rules('max:1000'),
            /** @phpstan-ignore-next-line It's what the docs say to do */
            Tag::make('Topics', 'topics', SupportTopic::class)->showCreateRelationButton()->preload(),

            ArticleContent::make('Content')
                ->stacked()
                ->fullWidth()
                ->alwaysShow()
                /** @phpstan-ignore-next-line It will always be a model */
                ->fillUsing(function (NovaRequest $request, SupportArticleModel $model, string $attribute, string $requestAttribute) {
                    $model->content = clean($request[$requestAttribute], 'youtube');

                    return $model->content;
                }),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function fieldsForIndex(NovaRequest $request): array
    {
        return [
            Text::make('Title', 'title')->displayUsing(fn (string $title) => Str::limit($title, 30))->sortable(),

            Badge::make('Status', function () {
                $liveVersion = $this->versions->firstWhere('live_at', '!=', null) ?? $this;

                return $liveVersion->status->value;
            })->map([
                ArticleStatus::PUBLISHED->value => 'success',
                ArticleStatus::DRAFT->value => 'info',
            ])->sortable(),

            Number::make('👀', 'views')->sortable(),
            Number::make('👍', 'thumbs_up')->sortable(),
            Number::make('👎', 'thumbs_down')->sortable(),

            Date::make('Created', 'created_at')->sortable(),
        ];
    }

    /**
     * @param  \App\Models\Support\SupportArticle  $model
     * @return void
     */
    public static function afterUpdate(NovaRequest $request, Model $model)
    {
        if ($model->wasChanged('content')) {
            $model->createVersion();
        }
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
        return [
            (new PublishArticle)
                ->canSeeWhen('update', $this)
                ->canRun(fn () => $this->latest_id === null)
                ->sole(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Support\SupportArticle>
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\Support\SupportArticle> $query */
        $query = static::traitIndexQuery($request, $query);

        return $query->withoutGlobalScope('published')
            ->withoutGlobalScope('live')
            ->withoutGlobalScope('public')
            ->whereNull('latest_id');
    }
}
