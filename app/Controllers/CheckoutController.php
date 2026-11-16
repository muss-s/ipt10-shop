<?php

namespace App\Controllers;

use App\Models\Order;
use App\Services\Cart;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class CheckoutController extends Controller
{
    public function show(Request $request): Response
    {
        $cart = Cart::details();
        if (!$cart['lines']) {
            Session::flash('error', 'Your cart is empty.');
            return $this->redirect('/cart');
        }
        return $this->view('checkout/show', ['title' => 'Checkout', 'cart' => $cart, 'user' => auth_user()]);
    }

    public function place(Request $request): Response
    {
        if ($fail = $this->validate($request, [
            'shipping_name' => 'required|max:100',
            'shipping_address' => 'required|min:10|max:255',
            'phone' => 'required|min:7|max:20',
        ], '/checkout')) {
            return $fail;
        }

        if (!Cart::items()) {
            return $this->redirect('/cart');
        }

        try {
            $orderId = Order::place(auth_user()['id'], Cart::items(), $request->only(['shipping_name', 'shipping_address', 'phone']));
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect('/cart');
        }

        Cart::clear();
        Session::flash('success', "Order #$orderId placed. Thank you!");
        return $this->redirect('/orders/' . $orderId);
    }
}
