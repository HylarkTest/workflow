<?php

declare(strict_types=1);

namespace Tests\Actions;

use Illuminate\Database\Eloquent\Model;
use Actions\Models\Contracts\SoftDeleteModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelWithoutAction extends Model implements SoftDeleteModel
{
    use SoftDeletes;

    protected $table = 'models';

    protected $guarded = [];
}
