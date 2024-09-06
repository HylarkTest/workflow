<?php

declare(strict_types=1);

namespace Hylark\SelectAndCreate;

use Laravel\Nova\Fields\Select;

class SelectAndCreate extends Select
{
    public $searchable = true;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'select-and-create';
}
