<?php

declare(strict_types=1);

namespace Mappings\Core\Currency;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\DatabaseManager;
use Mappings\Core\Currency\Concerns\ConvertsRates;
use Mappings\Core\Currency\Contracts\CurrencyRepository;

class DatabaseCurrencyRepository implements CurrencyRepository
{
    use ConvertsRates;

    protected Connection $db;

    protected FixerCurrencyRepository $gateway;

    /**
     * @var array<string, float>
     */
    protected array $rates;

    /**
     * @var array<string>
     */
    protected array $currencies;

    /**
     * DatabaseCurrencyRepository constructor.
     */
    public function __construct(DatabaseManager $db, FixerCurrencyRepository $gateway)
    {
        $this->db = $db->connection(config('mappings.currencies.database'));
        $this->gateway = $gateway;
    }

    /**
     * @throws \Exception
     */
    public function refresh(): void
    {
        $rows = [];

        $rates = $this->gateway->rates();

        foreach ($rates as $code => $rate) {
            $rows[] = [
                'code' => $code,
                'exchange_rate' => $rate,
            ];
        }

        $this->db->table('currencies')
            ->truncate();

        $this->db->table('currencies')
            ->insert($rows);
    }

    /**
     * @param  string  $base
     * @return array<string, float>
     */
    public function rates($base = 'EUR'): array
    {
        if (! $this->rates) {
            $this->fetchRates();
        }

        return $this->convertRates($this->rates, $base);
    }

    public function currency(string $code): Currency
    {
        return new Currency($code, $this->rates()[$code], $this);
    }

    public function currencies(): array
    {
        if (! isset($this->currencies)) {
            $this->currencies = $this->db->table('currencies')->pluck('code')->all();
        }

        return $this->currencies;
    }

    /**
     * @return array<string, float>
     */
    protected function fetchRates(): array
    {
        return Cache::remember(
            config('mappings.currencies.cache.key'),
            config('mappings.currencies.cache.ttl'),
            fn () => $this->db->table('currencies')->pluck('exchange_rate', 'code')->all()
        );
    }
}
