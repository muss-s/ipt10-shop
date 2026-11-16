<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CurrencyService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('home/index', [
            'title' => 'Home',
            'products' => Product::latest(4),
            'categories' => Category::all('name ASC'),
        ]);
    }

    /** GET /currency/{code}: remember the shopper's display currency. */
    public function setCurrency(Request $request): Response
    {
        $code = strtoupper($request->param('code'));
        if (in_array($code, (new CurrencyService())->supported(), true)) {
            Session::set('currency', $code);
        }
        return $this->back($request, '/products');
    }
}
