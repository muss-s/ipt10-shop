<?php

/**
 * Boots the framework: autoloader, env + config, database path, logger, views.
 * Returns the root path. Used by public/index.php, bin/migrate.php and tests.
 */

use Core\Config;
use Core\Logger;
use Core\View;

$root = dirname(__DIR__);

// PSR-4 style autoloader: Core\X -> framework/X.php, App\X -> app/X.php
spl_autoload_register(function (string $class) use ($root): void {
    $map = ['Core\\' => '/framework/', 'App\\' => '/app/'];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $root . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

require __DIR__ . '/helpers.php';
if (is_file($root . '/app/helpers.php')) {
    require $root . '/app/helpers.php';
}

Config::loadEnv($root . '/.env');
Config::loadDirectory($root . '/config');

date_default_timezone_set(Config::get('app.timezone', 'UTC'));
Logger::$file = $root . '/storage/logs/app.log';
View::$path = $root . '/app/Views';

return $root;
