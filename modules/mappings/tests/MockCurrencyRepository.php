<?php

declare(strict_types=1);

namespace Tests\Mappings;

use Mappings\Core\Currency\Currency;
use Mappings\Core\Currency\Concerns\ConvertsRates;
use Mappings\Core\Currency\Contracts\CurrencyRepository;

class MockCurrencyRepository implements CurrencyRepository
{
    use ConvertsRates;

    public function rates($base = 'EUR'): array
    {
        return $this->convertRates([
            'EUR' => 1, 'GBP' => 0.9, 'CAD' => 1.5, 'USD' => 1.2,
        ], $base);
    }

    public function currency(string $code): Currency
    {
        return new Currency($code, $this->rates()[$code], $this);
    }

    public function currencies(): array
    {
        return ['EUR', 'GBP', 'CAD', 'USD'];
    }
}
