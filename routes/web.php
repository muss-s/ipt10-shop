<?php

/** @var Core\Router $router */

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderAdminController;
use App\Controllers\Admin\ProductAdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;

// Public pages
$router->get('/', [HomeController::class, 'index']);
$router->get('/currency/{code}', [HomeController::class, 'setCurrency']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{slug}', [ProductController::class, 'show']);
$router->get('/api/products', [ProductController::class, 'apiIndex']);

// Cart (works for guests too)
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);

// Auth
$router->group(['middleware' => ['guest']], function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
});
$router->post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Signed-in customers
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/checkout', [CheckoutController::class, 'show']);
    $router->post('/checkout', [CheckoutController::class, 'place']);
    $router->get('/orders', [OrderController::class, 'index']);
    $router->get('/orders/{id}', [OrderController::class, 'show']);
});

// Admin area
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($router) {
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/products', [ProductAdminController::class, 'index']);
    $router->get('/products/create', [ProductAdminController::class, 'create']);
    $router->post('/products', [ProductAdminController::class, 'store']);
    $router->get('/products/{id}/edit', [ProductAdminController::class, 'edit']);
    $router->post('/products/{id}', [ProductAdminController::class, 'update']);
    $router->delete('/products/{id}', [ProductAdminController::class, 'destroy']);
    $router->get('/orders', [OrderAdminController::class, 'index']);
    $router->post('/orders/{id}/status', [OrderAdminController::class, 'updateStatus']);
});
