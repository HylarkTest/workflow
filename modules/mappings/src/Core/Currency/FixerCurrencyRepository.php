<?php

declare(strict_types=1);

namespace Mappings\Core\Currency;

use GuzzleHttp\Client;
use Mappings\Core\Currency\Concerns\ConvertsRates;
use Mappings\Core\Currency\Contracts\CurrencyRepository;

class FixerCurrencyRepository implements CurrencyRepository
{
    use ConvertsRates;

    protected string $accessKey;

    protected Client $client;

    /**
     * @var array<string, float>
     */
    protected array $rates;

    public function __construct(string $accessKey, Client $client)
    {
        $this->accessKey = $accessKey;
        $this->client = $client;
    }

    /**
     * @param  string  $base
     * @return array<string, float>
     *
     * @throws \Exception
     */
    public function rates($base = 'EUR'): array
    {
        if (! isset($this->rates)) {
            $this->fetchRates();
        }

        return $this->convertRates($this->rates, $base);
    }

    /**
     * @throws \Exception
     */
    public function currency(string $code): Currency
    {
        return new Currency($code, $this->rates()[$code], $this);
    }

    public function currencies(): array
    {
        return array_keys($this->rates('EUR'));
    }

    /**
     * @throws \Exception
     */
    protected function fetchRates(): void
    {
        $response = $this->client->get('http://data.fixer.io/api/latest', [
            'query' => [
                'access_key' => $this->accessKey,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        if (! $body['success']) {
            throw new \Exception($body['message'] ?? $response->getBody());
        }

        $this->rates = $body['rates'];
    }
}
