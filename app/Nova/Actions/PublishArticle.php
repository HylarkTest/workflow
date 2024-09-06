<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Models\Support\SupportArticle;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Illuminate\Database\Eloquent\Collection;

class PublishArticle extends DestructiveAction
{
    public function name()
    {
        return 'Publish';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>  $models
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $models->each(function (SupportArticle $article) {
            $article->publish();
        });

        /** @phpstan-ignore-next-line Nothing to see here */
        return Action::redirect('/nova/resources/support-articles/'.$models->first()->getKey());
    }
}
