<?php

declare(strict_types=1);

namespace App\Core\IPLocation;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class Position implements Arrayable
{
    /**
     * The IP address used to retrieve the location.
     */
    public string $ip;

    /**
     * The country name.
     */
    public ?string $countryName;

    /**
     * The country code.
     */
    public ?string $countryCode;

    /**
     * The region code.
     */
    public ?string $regionCode;

    /**
     * The region name.
     */
    public ?string $regionName;

    /**
     * The city name.
     */
    public ?string $cityName;

    /**
     * The zip code.
     */
    public ?string $zipCode;

    /**
     * The iso code.
     */
    public ?string $isoCode;

    /**
     * The postal code.
     */
    public ?string $postalCode;

    /**
     * The latitude.
     */
    public ?string $latitude;

    /**
     * The longitude.
     */
    public ?string $longitude;

    /**
     * The area code.
     */
    public ?string $areaCode;

    public ?string $timezone;

    /**
     * The driver used for retrieving the location.
     */
    public ?string $driver;

    public ?bool $isSuspicious = false;

    /**
     * Determine if the position is empty.
     */
    public function isEmpty(): bool
    {
        $data = $this->toArray();

        unset($data['ip'], $data['driver']);

        return empty(array_filter($data));
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
