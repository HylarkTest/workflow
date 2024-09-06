<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->where('id', '>', 100)->each(function (User $user) {
            Space::factory(random_int(1, 3))
                ->logo()
                ->for($user, 'owner')
                ->create();
        });
    }
}
