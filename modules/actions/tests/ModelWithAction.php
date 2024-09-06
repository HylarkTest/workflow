<?php

declare(strict_types=1);

namespace Tests\Actions;

use Actions\Models\Concerns\HasActions;
use Illuminate\Database\Eloquent\Model;
use Actions\Models\Contracts\ActionSubject;
use Actions\Models\Contracts\SoftDeleteModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelWithAction extends Model implements ActionSubject, SoftDeleteModel
{
    use HasActions;
    use SoftDeletes;

    protected $table = 'models';

    protected $guarded = [];
}
