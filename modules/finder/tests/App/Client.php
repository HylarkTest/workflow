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
final class Client extends Model implements GloballySearchable
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
        return new ClientFactory;
    }
}

final class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone_number' => $this->faker->unique()->e164PhoneNumber,
            'email' => $this->faker->unique()->email,
        ];
    }
}
