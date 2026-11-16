<h1>Orders</h1>
<?php if (!$orders): ?>
    <p class="empty">No orders yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="<?= url('/orders/' . $o['id']) ?>">#<?= (int) $o['id'] ?></a></td>
                <td><?= e($o['customer_name']) ?><br><span class="muted small"><?= e($o['customer_email']) ?></span></td>
                <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                <td><?= peso((int) $o['total_cents']) ?></td>
                <td>
                    <form method="post" action="<?= url('/admin/orders/' . $o['id'] . '/status') ?>" class="row">
                        <?= csrf_field() ?>
                        <select name="status" <?= $o['status'] === 'cancelled' ? 'disabled' : '' ?>>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $s === $o['status'] ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php if ($o['status'] !== 'cancelled'): ?><button class="btn btn-small btn-ghost" type="submit">Save</button><?php endif ?>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
