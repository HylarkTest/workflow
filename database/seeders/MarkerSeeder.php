<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Faker\Generator;
use App\Models\MarkerGroup;
use Illuminate\Database\Seeder;
use Actions\Core\Contracts\ActionRecorder;

class MarkerSeeder extends Seeder
{
    protected ActionRecorder $recorder;

    public function __construct(Generator $faker, ActionRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalResolver = $this->recorder->getUserResolver();
        User::query()->where('id', '>', 100)->each(function (User $user) {
            $this->recorder->setUserResolver(fn () => $user);
            MarkerGroup::factory(random_int(1, 2))
                ->for($user, 'owner')
                ->withMarkers()
                ->create();
        });

        $this->recorder->setUserResolver($originalResolver);
    }
}
