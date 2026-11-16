<?php

namespace App\Services;

use Core\Config;
use Core\HttpClient;
use Core\HttpClientException;
use Core\Logger;
use Core\Session;

/**
 * Converts peso prices to other currencies using the Frankfurter API
 * (https://frankfurter.dev). Rates are cached on disk so the API is called
 * at most once per cache_ttl, and stale rates are used if the API is down.
 */
class CurrencyService
{
    private string $cacheFile;
    private array $config;
    private ?array $rates = null;

    public function __construct(private ?HttpClient $http = null, ?string $cacheFile = null)
    {
        $this->config = Config::get('services.currency');
        $this->http ??= new HttpClient($this->config['timeout']);
        $this->cacheFile = $cacheFile ?? dirname(__DIR__, 2) . '/storage/cache/rates.json';
    }

    /** Currencies the shopper can pick, base currency first. */
    public function supported(): array
    {
        return [Config::get('app.base_currency'), ...$this->config['symbols']];
    }

    /** The shopper's chosen display currency (stored in the session). */
    public function current(): string
    {
        $code = Session::get('currency', Config::get('app.base_currency'));
        return in_array($code, $this->supported(), true) ? $code : Config::get('app.base_currency');
    }

    /**
     * Returns ['base' => 'PHP', 'date' => '2026-11-04', 'rates' => ['USD' => 0.017, ...], 'stale' => bool]
     * or null when no rates are available at all.
     */
    public function rates(): ?array
    {
        if ($this->rates !== null) {
            return $this->rates;
        }

        $cached = $this->readCache();
        if ($cached && time() - $cached['fetched_at'] < $this->config['cache_ttl']) {
            return $this->rates = $cached + ['stale' => false];
        }

        try {
            // GET /v1/latest?base=PHP&symbols=USD,EUR,JPY,SGD
            $data = $this->http->getJson($this->config['base_url'] . '/latest', [
                'base' => Config::get('app.base_currency'),
                'symbols' => implode(',', $this->config['symbols']),
            ]);

            if (!isset($data['rates']) || !is_array($data['rates'])) {
                throw new HttpClientException('Unexpected API response shape');
            }

            $fresh = [
                'base' => $data['base'] ?? Config::get('app.base_currency'),
                'date' => $data['date'] ?? date('Y-m-d'),
                'rates' => array_map('floatval', $data['rates']),
                'fetched_at' => time(),
            ];
            $this->writeCache($fresh);
            return $this->rates = $fresh + ['stale' => false];
        } catch (HttpClientException $e) {
            Logger::error('Currency API: ' . $e->getMessage());
            // Fall back to the last good rates, even if old.
            return $this->rates = $cached ? $cached + ['stale' => true] : null;
        }
    }

    /** Converts base-currency centavos to an amount in $currency, or null if no rate. */
    public function convert(int $cents, string $currency): ?float
    {
        $amount = $cents / 100;
        if ($currency === Config::get('app.base_currency')) {
            return $amount;
        }
        $rate = $this->rates()['rates'][$currency] ?? null;
        return $rate === null ? null : $amount * $rate;
    }

    /** Formatted price in the shopper's currency, falling back to pesos. */
    public function format(int $cents, ?string $currency = null): string
    {
        $currency ??= $this->current();
        $value = $this->convert($cents, $currency);
        if ($value === null) {
            return peso($cents);
        }
        $symbols = ['PHP' => '₱', 'USD' => '$', 'EUR' => '€', 'JPY' => '¥', 'SGD' => 'S$'];
        $decimals = $currency === 'JPY' ? 0 : 2;
        return ($symbols[$currency] ?? $currency . ' ') . number_format($value, $decimals);
    }

    private function readCache(): ?array
    {
        if (!is_file($this->cacheFile)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($this->cacheFile), true);
        return is_array($data) && isset($data['rates'], $data['fetched_at']) ? $data : null;
    }

    private function writeCache(array $data): void
    {
        @file_put_contents($this->cacheFile, json_encode($data), LOCK_EX);
    }
}
