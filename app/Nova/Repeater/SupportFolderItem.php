<?php

declare(strict_types=1);

namespace App\Nova\Repeater;

use Laravel\Nova\Fields\Text;
use App\Models\Support\SupportFolder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class SupportFolderItem extends Repeatable
{
    public static $model = SupportFolder::class;

    public static function label()
    {
        return 'Support Folder';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')->rules('required', 'max:1000'),
        ];
    }
}
