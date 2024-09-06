<?php

declare(strict_types=1);

namespace Database\Factories\Demo;

use Illuminate\Support\Str;
use Database\Factories\ItemFactory;

class EzekiaItemFactory extends ItemFactory
{
    public function projects(): self
    {
        return $this->state([
            'data' => [
                'name' => $this->faker->word,
                'projectIdId' => Str::random(),
                'descriptionId' => $this->faker->jobTitle,
            ],
        ]);
    }

    public function people(): self
    {
        return $this->state(function () {
            $gender = $this->faker->gender;

            return [
                'data' => [
                    'titleId' => $this->faker->title($gender),
                    'firstName' => $this->faker->firstName($gender),
                    'lastName' => $this->faker->lastName,
                    'pictureId' => $this->faker->croppedProfilePicture($gender),
                    'emailId' => $this->faker->safeEmail,
                    'locationId' => $this->faker->city,
                    'confidentialId' => [
                        'confidential_salaryId' => $this->faker->numberBetween(1, 100) * 10_000,
                        'confidential_bonusId' => $this->faker->numberBetween(0, 10) * 10,
                    ],
                    'positionsId' => array_map(fn () => [
                        'position_companyId' => $this->faker->company,
                        'position_titleId' => $this->faker->jobTitle,
                        'position_summaryId' => $this->faker->paragraph,
                        'position_skillsId' => array_map(fn () => $this->faker->sentence, range(0, $this->faker->numberBetween(0, 3))),
                        'position_achievementsId' => array_map(fn () => $this->faker->sentence, range(0, $this->faker->numberBetween(0, 3))),
                    ], range(0, $this->faker->numberBetween(1, 6))),
                ],
            ];
        });
    }

    public function companies(): self
    {
        return $this->state([
            'data' => [
                'name' => $this->faker->company,
                'logoId' => ['image' => $this->faker->storedLogo],
                'divisionId' => $this->faker->division,
                'industryId' => $this->faker->industry,
                'sizeId' => $this->faker->size,
            ],
        ]);
    }
}
