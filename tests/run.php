<?php

/**
 * Tiny test runner (no PHPUnit needed): php tests/run.php
 * Uses an in-memory SQLite database so real data is never touched.
 */

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Core\Config;
use Core\Database;
use Core\HttpException;
use Core\Request;
use Core\Router;
use Core\Validator;

$root = require dirname(__DIR__) . '/framework/bootstrap.php';

$passed = 0;
$failed = 0;
function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "  ✓ $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "  ✗ $name\n    " . $e->getMessage() . "\n";
    }
}
function assert_same(mixed $expected, mixed $actual, string $msg = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(($msg ? "$msg: " : '') . 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

echo "Router\n";
$router = new Router();
$router->get('/products/{slug}', fn () => 'show');
$router->post('/cart/add', fn () => 'add');
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($r) {
    $r->get('/', fn () => 'dash');
});

test('matches route params', function () use ($router) {
    [, $params] = $router->match('GET', '/products/clean-code');
    assert_same(['slug' => 'clean-code'], $params);
});
test('applies group prefix and middleware', function () use ($router) {
    [$route] = $router->match('GET', '/admin');
    assert_same(['auth', 'admin'], $route->middleware);
});
test('404 for unknown path', function () use ($router) {
    try {
        $router->match('GET', '/nope');
        throw new RuntimeException('no exception');
    } catch (HttpException $e) {
        assert_same(404, $e->status());
    }
});
test('405 for wrong method', function () use ($router) {
    try {
        $router->match('GET', '/cart/add');
        throw new RuntimeException('no exception');
    } catch (HttpException $e) {
        assert_same(405, $e->status());
    }
});

echo "Request\n";
test('normalises path and reads input', function () {
    $req = new Request('POST', '/cart/add/', [], ['quantity' => ' 2 ']);
    assert_same('/cart/add', $req->path());
    assert_same('2', $req->input('quantity'));
});

echo "Validator\n";
test('reports rule errors', function () {
    $v = new Validator(['email' => 'bad', 'price' => '-1', 'name' => ''], ['email' => 'required|email', 'price' => 'numeric|min:0', 'name' => 'required']);
    assert_same(['email', 'price', 'name'], array_keys($v->errors()));
});

echo "Database + models (in-memory SQLite)\n";
Database::setConnection(new Database(['driver' => 'sqlite', 'database' => ':memory:']));
Database::connection()->pdo()->exec(file_get_contents($root . '/database/migrations/001_create_tables.sql'));

test('register and authenticate a user', function () {
    User::register('Test', 'Test@Example.com', 'secret123');
    assert_same('test@example.com', User::attempt('test@example.com', 'secret123')['email']);
    assert_same(null, User::attempt('test@example.com', 'wrong'));
});
test('placing an order lowers stock', function () {
    $pid = Product::create(['name' => 'Mug', 'slug' => 'mug', 'price_cents' => 25000, 'stock' => 3]);
    $user = User::findBy('email', 'test@example.com');
    $orderId = Order::place((int) $user['id'], [$pid => 2], ['shipping_name' => 'T', 'shipping_address' => 'Somewhere 123', 'phone' => '0917']);
    assert_same(50000, (int) Order::find($orderId)['total_cents']);
    assert_same(1, (int) Product::find($pid)['stock']);
});
test('order fails and rolls back when stock is short', function () {
    $pid = (int) Product::findBy('slug', 'mug')['id'];
    $before = Order::count();
    try {
        Order::place(1, [$pid => 5], ['shipping_name' => 'T', 'shipping_address' => 'Somewhere 123', 'phone' => '0917']);
        throw new RuntimeException('no exception');
    } catch (DomainException) {
    }
    assert_same($before, Order::count());
});
test('unique slugs', function () {
    assert_same('mug-2', Product::uniqueSlug('Mug'));
});

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
