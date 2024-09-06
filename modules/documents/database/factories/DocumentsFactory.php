<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;
use Documents\Models\Document;

$factory->define(Document::class, static function (Faker $faker) {
    return [
        'filename' => $faker->word,
        'url' => $faker->url,
        'size' => 1024,
    ];
});
