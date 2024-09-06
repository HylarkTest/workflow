<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

use Illuminate\Support\Fluent;
use App\Core\IPLocation\Position;

class IpInfo extends Driver
{
    protected function url(string $ip): string
    {
        $url = "https://ipinfo.io/$ip";

        if ($token = $this->config->get('location.ipinfo.token')) {
            $url .= '?token='.$token;
        }

        return $url;
    }

    protected function hydrate(Position $position, Fluent $location): Position
    {
        $position->countryCode = $location->country;
        $position->regionName = $location->region;
        $position->cityName = $location->city;
        $position->zipCode = $location->postal;
        $position->timezone = $location->timezone;

        if ($location->loc) {
            $coords = explode(',', $location->loc);

            if (\array_key_exists(0, $coords)) {
                $position->latitude = $coords[0];
            }

            if (\array_key_exists(1, $coords)) {
                $position->longitude = $coords[1];
            }
        }

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
