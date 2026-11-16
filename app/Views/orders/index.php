<h1>My orders</h1>
<?php if (!$orders): ?>
    <p class="empty">No orders yet. <a href="<?= url('/products') ?>">Start shopping</a>.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><a href="<?= url('/orders/' . $order['id']) ?>">#<?= (int) $order['id'] ?></a></td>
                <td><?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></td>
                <td><span class="tag tag-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></td>
                <td><?= peso((int) $order['total_cents']) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
