<?php

namespace App\Services;

use App\Models\Product;
use Core\Session;

/**
 * Shopping cart kept in the session as [product_id => quantity].
 */
class Cart
{
    public static function items(): array
    {
        return Session::get('cart', []);
    }

    public static function add(int $productId, int $qty = 1): void
    {
        $cart = self::items();
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $qty);
        Session::set('cart', $cart);
    }

    public static function update(int $productId, int $qty): void
    {
        $cart = self::items();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        Session::set('cart', $cart);
    }

    public static function remove(int $productId): void
    {
        self::update($productId, 0);
    }

    public static function clear(): void
    {
        Session::forget('cart');
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    /**
     * Cart lines joined with product data:
     * ['lines' => [[product, qty, subtotal_cents], ...], 'total_cents' => int]
     * Products that were deleted are dropped from the cart.
     */
    public static function details(): array
    {
        $cart = self::items();
        $products = Product::findMany(array_keys($cart));
        $lines = [];
        $total = 0;

        foreach ($cart as $id => $qty) {
            if (!isset($products[$id])) {
                self::remove($id);
                continue;
            }
            $subtotal = $products[$id]['price_cents'] * $qty;
            $lines[] = ['product' => $products[$id], 'qty' => $qty, 'subtotal_cents' => $subtotal];
            $total += $subtotal;
        }

        return ['lines' => $lines, 'total_cents' => $total];
    }
}
