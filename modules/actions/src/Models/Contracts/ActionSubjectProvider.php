<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Models\Action;
use Illuminate\Database\Eloquent\Model;

interface ActionSubjectProvider
{
    public function getActionSubject(Action $action): Model;
}
