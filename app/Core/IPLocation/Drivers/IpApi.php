<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

use Illuminate\Support\Fluent;
use App\Core\IPLocation\Position;

class IpApi extends Driver
{
    protected function url(string $ip): string
    {
        return "https://ip-api.com/json/$ip";
    }

    protected function hydrate(Position $position, Fluent $location): Position
    {
        $position->countryName = $location->country;
        $position->countryCode = $location->countryCode;
        $position->regionCode = $location->region;
        $position->regionName = $location->regionName;
        $position->cityName = $location->city;
        $position->zipCode = $location->zip;
        $position->latitude = (string) $location->lat;
        $position->longitude = (string) $location->lon;
        $position->areaCode = $location->region;
        $position->timezone = $location->timezone;

        return $position;
    }

    protected function process(string $ip): bool|Fluent
    {
        try {
            $response = json_decode($this->getUrlContent($this->url($ip)), true, 512, \JSON_THROW_ON_ERROR);

            return new Fluent($response);
        } catch (\Exception $e) {
            return false;
        }
    }
}
