<?php

namespace App\Controllers\Admin;

use App\Models\Order;
use Core\Controller;
use Core\Database;
use Core\HttpException;
use Core\Request;
use Core\Response;
use Core\Session;

class OrderAdminController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/orders/index', [
            'title' => 'Manage orders',
            'orders' => Order::allWithCustomer(),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(Request $request): Response
    {
        $order = Order::find((int) $request->param('id')) ?? throw new HttpException(404, 'Order not found.');
        $status = $request->input('status');

        if (!in_array($status, Order::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            return $this->redirect('/admin/orders');
        }

        Database::connection()->transaction(function (Database $db) use ($order, $status) {
            // Cancelling puts the items back in stock (once).
            if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
                foreach (Order::items($order['id']) as $item) {
                    $db->query('UPDATE products SET stock = stock + ? WHERE id = ?', [$item['quantity'], $item['product_id']]);
                }
            }
            Order::update($order['id'], ['status' => $status]);
        });

        Session::flash('success', "Order #{$order['id']} is now $status.");
        return $this->redirect('/admin/orders');
    }
}
