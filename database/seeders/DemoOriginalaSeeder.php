<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoOriginalaSeeder extends Seeder
{
    use SeedsJsonFiles;

    protected \Faker\Generator $faker;

    /**
     * Run the database seeds.
     */
    public function run(\Faker\Generator $faker): void
    {
        $this->faker = $faker;

        $executive = factory(User::class)->states('avatar')->create();

        $this->seedDirectory(__DIR__.'/../mappings/executives-place', $executive);

        $regularUser = factory(User::class)->states('avatar')->create();

        $this->seedDirectory(__DIR__.'/../mappings/regular', $regularUser);

        $this->call(EzekiaSeeder::class);
    }
}
