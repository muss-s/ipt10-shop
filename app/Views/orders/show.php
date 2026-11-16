<a class="muted small" href="<?= url(auth_user()['role'] === 'admin' ? '/admin/orders' : '/orders') ?>">← Back to orders</a>
<h1>Order #<?= (int) $order['id'] ?> <span class="tag tag-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></h1>
<p class="muted">Placed <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>

<table class="table">
    <thead><tr><th>Product</th><th>Unit price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= $item['slug'] ? '<a href="' . url('/products/' . $item['slug']) . '">' . e($item['name']) . '</a>' : 'Deleted product' ?></td>
            <td><?= peso((int) $item['unit_price_cents']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td><?= peso($item['unit_price_cents'] * $item['quantity']) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot><tr><th colspan="3">Total</th><th><?= peso((int) $order['total_cents']) ?></th></tr></tfoot>
</table>

<h3>Ship to</h3>
<p><?= e($order['shipping_name']) ?><br><?= nl2br(e($order['shipping_address'])) ?><br><?= e($order['phone']) ?></p>
