<?php

declare(strict_types=1);

namespace Mappings\Core\Currency;

use Mappings\Core\Currency\Contracts\CurrencyRepository;

class Currency
{
    protected static array $symbols;

    protected string $code;

    protected float $rate;

    protected CurrencyRepository $currencies;

    public function __construct(string $code, float $rate, CurrencyRepository $currencies)
    {
        $this->code = $code;
        $this->rate = $rate;
        $this->currencies = $currencies;
    }

    public function symbol(): string
    {
        return static::symbolMap()[$this->code] ?? $this->code;
    }

    public static function symbolMap(): array
    {
        if (isset(static::$symbols)) {
            return static::$symbols;
        }

        return static::$symbols = json_decode(file_get_contents(
            implode(\DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', 'resources', 'currency_symbols.json'])
        ) ?: '', true) ?: [];
    }
}
