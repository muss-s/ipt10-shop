<?php

use Core\Config;

return [
    'name' => Config::env('APP_NAME', 'KAGE 影 SUPPLY'),
    'url' => Config::env('APP_URL', 'http://localhost:8000'),
    'debug' => Config::env('APP_DEBUG', false),
    'timezone' => Config::env('APP_TIMEZONE', 'Asia/Manila'),
    'base_currency' => 'PHP',
];
