<?php

/**
 * Front controller: every request enters here.
 */

use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RequestLogMiddleware;
use App\Middleware\SessionMiddleware;
use Core\App;
use Core\Router;

// Let PHP's built-in server serve static files (css, images) directly.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/framework/bootstrap.php';

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

$app = new App($router, dirname(__DIR__));

$app->use(RequestLogMiddleware::class);
$app->use(SessionMiddleware::class);
$app->use(CsrfMiddleware::class);

$app->aliasMiddleware([
    'auth' => AuthMiddleware::class,
    'admin' => AdminMiddleware::class,
    'guest' => GuestMiddleware::class,
]);

$app->run();
