<?php

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CurrencyService;
use Core\Controller;
use Core\Database;
use Core\Request;
use Core\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();
        return $this->view('admin/dashboard', [
            'title' => 'Admin',
            'stats' => [
                'products' => Product::count(),
                'orders' => Order::count(),
                'customers' => (int) $db->fetch("SELECT COUNT(*) AS n FROM users WHERE role = 'customer'")['n'],
                'revenue_cents' => (int) $db->fetch("SELECT COALESCE(SUM(total_cents), 0) AS n FROM orders WHERE status != 'cancelled'")['n'],
            ],
            'lowStock' => $db->fetchAll('SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC'),
            'rates' => (new CurrencyService())->rates(),
        ]);
    }
}
