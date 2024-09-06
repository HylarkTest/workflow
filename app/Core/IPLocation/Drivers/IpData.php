<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use App\Core\IPLocation\Position;

class IpData extends Driver
{
    protected function url(string $ip): string
    {
        $token = $this->config->get('location.ipdata.token', '');

        return "https://api.ipdata.co/{$ip}?api-key={$token}";
    }

    protected function hydrate(Position $position, Fluent $location): Position
    {
        $position->countryName = $location->country_name;
        $position->countryCode = $location->country_code;
        $position->regionCode = $location->region_code;
        $position->regionName = $location->region;
        $position->cityName = $location->city;
        $position->zipCode = $location->postal;
        $position->postalCode = $location->postal;
        $position->latitude = (string) $location->latitude;
        $position->longitude = (string) $location->longitude;
        $position->timezone = $location->time_zone['name'] ?? null;

        // Bogon IPs are IP addresses that should never appear on the public Internet.
        $suspiciousThreatKeys = ['is_known_attacker', 'is_known_abuser', 'is_bogon', 'is_threat'];

        if (Arr::first($suspiciousThreatKeys, fn (string $key): bool => ($location->threat[$key] ?? false) === true)) {
            $position->isSuspicious = true;
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
