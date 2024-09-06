<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Actions\ActionResponse;
use Illuminate\Database\Eloquent\Collection;

class RestoreVersion extends Action
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Support\SupportArticle>  $models
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var \App\Models\Support\SupportArticle $version */
        $version = $models->first();
        /** @var \App\Models\Support\SupportArticle $parent */
        $parent = $version->parent;
        $parent->update([
            'title' => $version->title,
            'content' => $version->content,
            'folder_id' => $version->folder_id,
        ]);
        $parent->createVersion();

        /** @phpstan-ignore-next-line Nothing to see here */
        return Action::redirect('/nova/resources/support-articles/'.$version->parent->getKey());
    }
}
