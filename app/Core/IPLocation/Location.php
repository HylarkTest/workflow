<?php

declare(strict_types=1);

namespace App\Core\IPLocation;

use App\Core\IPLocation\Drivers\Driver;
use Illuminate\Contracts\Config\Repository;
use App\Core\IPLocation\Exceptions\DriverDoesNotExistException;

class Location
{
    /**
     * The current driver.
     */
    protected Driver $driver;

    /**
     * The application configuration.
     */
    protected Repository $config;

    /**
     * Constructor.
     *
     * @throws \App\Core\IPLocation\Exceptions\DriverDoesNotExistException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $this->setDefaultDriver();
    }

    /**
     * Set the current driver to use.
     */
    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Set the default location driver to use.
     *
     * @throws \App\Core\IPLocation\Exceptions\DriverDoesNotExistException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setDefaultDriver(): void
    {
        $driver = $this->getDriver($this->getDefaultDriver());

        foreach ($this->getDriverFallbacks() as $fallback) {
            $driver->fallback($this->getDriver($fallback));
        }

        $this->setDriver($driver);
    }

    /**
     * Attempt to retrieve the location of the user.
     *
     * @return \App\Core\IPLocation\Position|false
     *
     * @throws \Exception
     */
    public function get(?string $ip = null): bool|Position
    {
        if (! $ip) {
            $ip = $this->getClientIP();
        }
        if ($ip && $location = $this->driver->get($ip)) {
            return $location;
        }

        return false;
    }

    /**
     * Get the client IP address.
     */
    protected function getClientIP(): ?string
    {
        return $this->localHostTesting()
            ? $this->getLocalHostTestingIp()
            : request()->ip();
    }

    /**
     * Determine if testing is enabled.
     */
    protected function localHostTesting(): bool
    {
        return $this->config->get('location.testing.enabled', true);
    }

    /**
     * Get the testing IP address.
     */
    protected function getLocalHostTestingIp(): string
    {
        return $this->config->get('location.testing.ip', '66.102.0.0');
    }

    /**
     * Get the fallback location drivers to use.
     */
    protected function getDriverFallbacks(): array
    {
        return $this->config->get('location.fallbacks', []);
    }

    /**
     * Get the default location driver.
     */
    protected function getDefaultDriver(): string
    {
        return $this->config->get('location.driver');
    }

    /**
     * Attempt to create the location driver.
     *
     * @throws \App\Core\IPLocation\Exceptions\DriverDoesNotExistException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getDriver(string $driver): Driver
    {
        if (! class_exists($driver)) {
            throw DriverDoesNotExistException::forDriver($driver);
        }

        return app()->make($driver);
    }
}
