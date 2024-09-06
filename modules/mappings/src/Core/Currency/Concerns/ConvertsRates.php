<?php

declare(strict_types=1);

namespace Mappings\Core\Currency\Concerns;

trait ConvertsRates
{
    /**
     * @param  array<string, float>  $rates
     * @return array<string, float>
     */
    protected function convertRates(array $rates, string $base)
    {
        if ($base !== 'EUR') {
            $newRate = $rates[$base];
            $multiplier = 1 / $newRate;
            foreach ($rates as $currency => $rate) {
                if ($currency === $base) {
                    $rates[$currency] = 1;
                } else {
                    $rates[$currency] = $rate * $multiplier;
                }
            }
        }

        return $rates;
    }
}
