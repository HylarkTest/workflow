<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Models\Action;
use App\Models\Contracts\NotScoped;

class UndistributedAction extends Action implements NotScoped
{
    protected $table = 'undistributed_actions';
}
