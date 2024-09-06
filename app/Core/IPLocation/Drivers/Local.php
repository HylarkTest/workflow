<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

use Illuminate\Support\Fluent;
use App\Core\IPLocation\Position;

class Local extends Driver
{
    public function get(string $ip): bool|Position
    {
        $position = new Position;

        $position->timezone = 'Europe/London';
        $position->countryName = 'United Kingdom';
        $position->cityName = 'Farnborough';
        $position->ip = $ip;
        $position->areaCode = 'ENG';
        $position->countryCode = 'GB';
        $position->driver = static::class;
        $position->regionCode = 'ENG';
        $position->regionName = 'England';
        // Useful for testing so different IPs trigger different locations
        $position->latitude = $ip === '127.0.0.1' ? '51.2699' : '41';
        $position->longitude = $ip === '127.0.0.1' ? '-0.7313' : '29';
        $position->zipCode = 'GU11';

        return $position;
    }

    protected function url(string $ip): string
    {
        return '';
    }

    /**
     * @param  \Illuminate\Support\Fluent<string, mixed>  $location
     */
    protected function hydrate(Position $position, Fluent $location): Position
    {
        return new Position;
    }

    /**
     * @return \Illuminate\Support\Fluent<string, mixed>|bool
     */
    protected function process(string $ip): Fluent|bool
    {
        return false;
    }
}
