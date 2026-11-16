<?php

namespace App\Controllers;

use App\Models\Order;
use Core\Controller;
use Core\HttpException;
use Core\Request;
use Core\Response;

/**
 * A customer's own order history.
 */
class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('orders/index', [
            'title' => 'My Orders',
            'orders' => Order::where('user_id', auth_user()['id']),
        ]);
    }

    public function show(Request $request): Response
    {
        $order = Order::find((int) $request->param('id'));
        $user = auth_user();

        // Customers may only see their own orders; admins see all.
        if (!$order || ((int) $order['user_id'] !== $user['id'] && $user['role'] !== 'admin')) {
            throw new HttpException(404, 'Order not found.');
        }

        return $this->view('orders/show', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => Order::items($order['id']),
        ]);
    }
}
