<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

class IpApiPro extends IpApi
{
    protected function url(string $ip): string
    {
        $key = $this->config->get('location.ip_api.token');

        return "https://pro.ip-api.com/json/$ip?key=$key";
    }
}
