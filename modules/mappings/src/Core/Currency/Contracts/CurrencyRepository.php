<?php

declare(strict_types=1);

namespace Mappings\Core\Currency\Contracts;

use Mappings\Core\Currency\Currency;

interface CurrencyRepository
{
    /**
     * @param  string  $base
     */
    public function rates($base = 'EUR'): array;

    public function currency(string $code): Currency;

    /**
     * @return string[]
     */
    public function currencies(): array;
}
