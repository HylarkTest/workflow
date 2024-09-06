<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;
use App\Models\Contracts\Domain;

class InviteSeeder extends Seeder
{
    protected Generator $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userInviters = User::query()->where('id', '>', 100)
            ->with('mappings', 'spaces')->get();

        User::query()
            ->where('id', '>', 100)
            ->each(function (User $user) use ($userInviters) {
                $userInviters->random($this->faker->numberBetween(0, 2))->each(function (User $inviter) use ($user) {
                    if ($inviter->is($user)) {
                        return;
                    }
                    $inviter->mappings->merge($inviter->spaces)
                        ->random($this->faker->numberBetween(1, 2))
                        ->each(fn (Domain $domain) => $inviter->invite($user, $domain, $this->faker->boolean(80)));
                });
            });
    }
}
