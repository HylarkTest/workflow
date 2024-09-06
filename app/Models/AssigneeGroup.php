<?php

declare(strict_types=1);

namespace App\Models;

use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_default
 */
class AssigneeGroup extends Model
{
    use ConvertsCamelCaseAttributes;

    protected $fillable = [
        'name',
        'is_default',
    ];
}
