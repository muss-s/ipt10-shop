<?php

use Core\Config;

$root = dirname(__DIR__);
$driver = Config::env('DB_DRIVER', 'sqlite');
$database = Config::env('DB_DATABASE', 'storage/database/shop.sqlite');

// Relative SQLite paths are resolved from the project root.
if ($driver === 'sqlite' && !str_starts_with($database, '/') && $database !== ':memory:') {
    $database = $root . '/' . $database;
}

return [
    'driver' => $driver,
    'host' => Config::env('DB_HOST', '127.0.0.1'),
    'port' => Config::env('DB_PORT', 3306),
    'database' => $database,
    'username' => Config::env('DB_USERNAME', 'root'),
    'password' => Config::env('DB_PASSWORD', ''),
];
