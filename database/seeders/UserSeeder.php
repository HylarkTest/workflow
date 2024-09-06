<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(random_int(21, 55))
            ->state(new Sequence(
                fn (Sequence $sequence) => ['id' => 100 + $sequence->index]
            ))
            ->create();
    }
}
