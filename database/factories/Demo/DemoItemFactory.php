<?php

declare(strict_types=1);

namespace Database\Factories\Demo;

use Database\Factories\ItemFactory;

class DemoItemFactory extends ItemFactory
{
    public function people(): self
    {
        $gender = $this->faker->gender;

        return $this->state([
            'data' => [
                'titleId' => $this->faker->title($gender),
                'firstName' => $this->faker->firstName($gender),
                'lastName' => $this->faker->lastName,
                'imageId' => $this->faker->croppedProfilePicture($gender),
                'emailId' => $this->faker->safeEmail,
                'companyId' => $this->faker->company,
            ],
        ]);
    }

    public function projects(): self
    {
        return $this->state([
            'data' => [
                'name' => $faker->company.' : '.$faker->jobTitle,
                'description' => $faker->paragraph,
            ],
        ]);
    }
}
