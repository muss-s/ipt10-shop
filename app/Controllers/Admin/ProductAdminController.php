<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use Core\Controller;
use Core\Database;
use Core\HttpException;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Product management (create, edit, delete) for admins.
 */
class ProductAdminController extends Controller
{
    private const RULES = [
        'name' => 'required|max:150',
        'category_id' => 'required|integer',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'description' => 'max:2000',
        'image_url' => 'max:500',
    ];

    public function index(Request $request): Response
    {
        return $this->view('admin/products/index', [
            'title' => 'Manage products',
            'products' => Product::search(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/products/form', [
            'title' => 'New product',
            'product' => null,
            'categories' => Category::all('name ASC'),
        ]);
    }

    public function store(Request $request): Response
    {
        if ($fail = $this->validate($request, self::RULES, '/admin/products/create')) {
            return $fail;
        }
        $data = $this->payload($request);
        $data['slug'] = Product::uniqueSlug($data['name']);
        Product::create($data);
        Session::flash('success', 'Product created.');
        return $this->redirect('/admin/products');
    }

    public function edit(Request $request): Response
    {
        return $this->view('admin/products/form', [
            'title' => 'Edit product',
            'product' => $this->findOrFail($request),
            'categories' => Category::all('name ASC'),
        ]);
    }

    public function update(Request $request): Response
    {
        $product = $this->findOrFail($request);
        if ($fail = $this->validate($request, self::RULES, "/admin/products/{$product['id']}/edit")) {
            return $fail;
        }
        $data = $this->payload($request);
        $data['slug'] = Product::uniqueSlug($data['name'], (int) $product['id']);
        Product::update($product['id'], $data);
        Session::flash('success', 'Product updated.');
        return $this->redirect('/admin/products');
    }

    public function destroy(Request $request): Response
    {
        $product = $this->findOrFail($request);
        $used = Database::connection()->fetch('SELECT 1 FROM order_items WHERE product_id = ? LIMIT 1', [$product['id']]);
        if ($used) {
            // Keep order history intact: products that were ordered cannot be deleted.
            Session::flash('error', 'This product appears in orders. Set its stock to 0 instead.');
            return $this->redirect('/admin/products');
        }
        Product::delete($product['id']);
        Session::flash('success', 'Product deleted.');
        return $this->redirect('/admin/products');
    }

    private function findOrFail(Request $request): array
    {
        return Product::find((int) $request->param('id')) ?? throw new HttpException(404, 'Product not found.');
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'category_id' => (int) $request->input('category_id'),
            'price_cents' => (int) round((float) $request->input('price') * 100),
            'stock' => (int) $request->input('stock'),
            'description' => $request->input('description', ''),
            'image_url' => $request->input('image_url', ''),
        ];
    }
}
