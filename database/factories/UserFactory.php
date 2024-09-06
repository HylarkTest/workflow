<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $gender = $this->faker->gender;

        return [
            'name' => $this->faker->name($gender),
            'avatar' => $this->faker->profilePictureUrl($gender),
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'finished_registration_at' => now(),
            /* cspell:disable-next-line */
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function avatar(): self
    {
        $gender = $this->faker->gender;

        return $this->state([
            'name' => $this->faker->name($gender),
            'avatar' => $this->faker->storedProfilePicture($gender),
        ]);
    }
}
