<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Imports\ImportItemStatus;

class ImportMap extends Model
{
    protected $table = 'imports_map';

    protected $casts = [
        'status' => ImportItemStatus::class,
    ];

    protected $guarded = [];
}
