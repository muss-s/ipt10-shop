<?php

namespace App\Models;

use Core\Database;
use Core\Model;

class Order extends Model
{
    protected static string $table = 'orders';
    protected static array $fillable = ['user_id', 'status', 'total_cents', 'shipping_name', 'shipping_address', 'phone'];

    public const STATUSES = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    /**
     * Creates the order and its items, and lowers stock, all in one transaction.
     * $cart is [product_id => quantity]. Throws if any item is out of stock.
     */
    public static function place(int $userId, array $cart, array $shipping): int
    {
        return Database::connection()->transaction(function (Database $db) use ($userId, $cart, $shipping) {
            $products = Product::findMany(array_keys($cart));
            $total = 0;

            foreach ($cart as $productId => $qty) {
                $product = $products[$productId] ?? null;
                if (!$product) {
                    throw new \DomainException('A product in your cart no longer exists.');
                }
                if ($product['stock'] < $qty) {
                    throw new \DomainException("Only {$product['stock']} left of {$product['name']}.");
                }
                $total += $product['price_cents'] * $qty;
            }

            $orderId = self::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_cents' => $total,
                'shipping_name' => $shipping['shipping_name'],
                'shipping_address' => $shipping['shipping_address'],
                'phone' => $shipping['phone'],
            ]);

            foreach ($cart as $productId => $qty) {
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price_cents' => $products[$productId]['price_cents'],
                ]);
                // "AND stock >= ?" guards against two checkouts racing for the last item.
                $updated = $db->query(
                    'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
                    [$qty, $productId, $qty],
                )->rowCount();
                if ($updated === 0) {
                    throw new \DomainException("{$products[$productId]['name']} just sold out.");
                }
            }

            return $orderId;
        });
    }

    public static function items(int $orderId): array
    {
        return self::db()->fetchAll(
            'SELECT oi.*, p.name, p.slug FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?',
            [$orderId],
        );
    }

    public static function allWithCustomer(): array
    {
        return self::db()->fetchAll(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC',
        );
    }
}
