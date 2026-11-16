<?php

use Core\Config;

return [
    'currency' => [
        // Frankfurter: free, keyless exchange rates from the European Central Bank.
        'base_url' => Config::env('CURRENCY_API_URL', 'https://api.frankfurter.dev/v1'),
        'symbols' => ['USD', 'EUR', 'JPY', 'SGD'],
        'cache_ttl' => (int) Config::env('CURRENCY_CACHE_TTL', 3600),
        'timeout' => 5,
    ],
];
