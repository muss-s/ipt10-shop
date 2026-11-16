<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CurrencyService;
use Core\Controller;
use Core\HttpException;
use Core\Request;
use Core\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $term = $request->query('q');
        $category = $request->query('category');

        return $this->view('products/index', [
            'title' => 'Products',
            'products' => Product::search($term, $category),
            'categories' => Category::all('name ASC'),
            'term' => $term,
            'activeCategory' => $category,
        ]);
    }

    public function show(Request $request): Response
    {
        $product = Product::findBySlug($request->param('slug'));
        if (!$product) {
            throw new HttpException(404, 'Product not found.');
        }

        // Show the price in every supported currency on the detail page.
        $currency = new CurrencyService();
        $prices = [];
        foreach ($currency->supported() as $code) {
            if ($currency->convert($product['price_cents'], $code) !== null) {
                $prices[$code] = $currency->format($product['price_cents'], $code);
            }
        }

        return $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'prices' => $prices,
            'rates' => $currency->rates(),
        ]);
    }

    /** GET /api/products: JSON catalog, prices converted to ?currency=USD. */
    public function apiIndex(Request $request): Response
    {
        $currency = new CurrencyService();
        $code = strtoupper((string) $request->query('currency', config('app.base_currency')));
        if (!in_array($code, $currency->supported(), true)) {
            return $this->json(['error' => 'Unsupported currency', 'supported' => $currency->supported()], 422);
        }

        $items = array_map(fn ($p) => [
            'id' => (int) $p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'category' => $p['category_name'],
            'stock' => (int) $p['stock'],
            'price' => $currency->convert($p['price_cents'], $code),
            'currency' => $code,
        ], Product::search($request->query('q')));

        return $this->json(['data' => $items, 'rates_date' => $currency->rates()['date'] ?? null]);
    }
}
