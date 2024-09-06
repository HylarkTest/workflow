<?php

declare(strict_types=1);

namespace App\Providers;

use Faker\Generator;
use Database\Providers\EnumProvider;
use Database\Providers\ICalProvider;
use Database\Providers\LogoProvider;
use Database\Providers\CompanyProvider;
use Database\Providers\MappingProvider;
use Illuminate\Support\ServiceProvider;
use Database\Providers\TailwindProvider;
use Database\Providers\FontAwesomeProvider;
// use NewAgeIpsum\NewAgeProvider;
use Database\Providers\ProfilePictureProvider;

class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     */
    public function boot(Generator $faker): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }
        //        $faker->addProvider(new NewAgeProvider($faker));
        $faker->addProvider(new ProfilePictureProvider($faker));
        $faker->addProvider(new FontAwesomeProvider($faker));
        $faker->addProvider(new TailwindProvider($faker));
        $faker->addProvider(new LogoProvider($faker));
        $faker->addProvider(new MappingProvider($faker));
        $faker->addProvider(new CompanyProvider($faker));
        $faker->addProvider(new EnumProvider($faker));
        $faker->addProvider(new ICalProvider($faker));
    }
}
