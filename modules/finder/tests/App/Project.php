<?php

declare(strict_types=1);

namespace Tests\Finder\App;

use Carbon\Carbon;
use Finder\GloballySearchable;
use Finder\CanBeGloballySearched;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $phone_number
 * @property string $email
 * @property Carbon $deleted_at
 */
final class Project extends Model implements GloballySearchable
{
    use CanBeGloballySearched;
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $hidden = [
        'deleted_at',
    ];

    public function toGloballySearchableArray(): array
    {
        return [
            'id' => $this->id,
            'primary' => [
                'text' => $this->name,
                'map' => 'name',
            ],
        ];
    }

    protected static function newFactory()
    {
        return new ProjectFactory;
    }
}

final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName,
        ];
    }
}
