<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Todoable extends Pivot
{
    public $incrementing = true;

    protected $table = 'todoables';
}
