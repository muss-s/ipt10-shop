<?php

namespace App\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('cart/index', ['title' => 'Cart', 'cart' => Cart::details()]);
    }

    public function add(Request $request): Response
    {
        $product = Product::find((int) $request->input('product_id'));
        $qty = max(1, (int) $request->input('quantity', 1));

        if (!$product) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('/products');
        }

        $inCart = Cart::items()[$product['id']] ?? 0;
        if ($inCart + $qty > $product['stock']) {
            Session::flash('error', "Sorry, only {$product['stock']} in stock.");
            return $this->back($request, '/products');
        }

        Cart::add($product['id'], $qty);
        Session::flash('success', "{$product['name']} added to cart.");
        return $this->back($request, '/cart');
    }

    public function update(Request $request): Response
    {
        $product = Product::find((int) $request->input('product_id'));
        $qty = (int) $request->input('quantity', 0);

        if ($product) {
            Cart::update($product['id'], min($qty, (int) $product['stock']));
            if ($qty > $product['stock']) {
                Session::flash('error', "Only {$product['stock']} of {$product['name']} available.");
            }
        }
        return $this->redirect('/cart');
    }

    public function remove(Request $request): Response
    {
        Cart::remove((int) $request->input('product_id'));
        return $this->redirect('/cart');
    }
}
