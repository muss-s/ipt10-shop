<h1>Admin dashboard</h1>
<div class="admin-links">
    <a class="btn" href="<?= url('/admin/products') ?>">Manage products</a>
    <a class="btn btn-ghost" href="<?= url('/admin/orders') ?>">Manage orders</a>
</div>

<div class="stats">
    <div class="stat"><span class="muted">Products</span><strong><?= $stats['products'] ?></strong></div>
    <div class="stat"><span class="muted">Orders</span><strong><?= $stats['orders'] ?></strong></div>
    <div class="stat"><span class="muted">Customers</span><strong><?= $stats['customers'] ?></strong></div>
    <div class="stat"><span class="muted">Revenue</span><strong><?= peso($stats['revenue_cents']) ?></strong></div>
</div>

<div class="two-col">
    <section>
        <h2>Low stock (5 or fewer)</h2>
        <?php if (!$lowStock): ?>
            <p class="muted">All products are well stocked.</p>
        <?php else: ?>
            <table class="table compact">
                <?php foreach ($lowStock as $p): ?>
                    <tr><td><a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>"><?= e($p['name']) ?></a></td><td><?= (int) $p['stock'] ?> left</td></tr>
                <?php endforeach ?>
            </table>
        <?php endif ?>
    </section>
    <section>
        <h2>Exchange rates (1 PHP =)</h2>
        <?php if (!$rates): ?>
            <p class="error">Currency API unreachable and no cached rates.</p>
        <?php else: ?>
            <table class="table compact">
                <?php foreach ($rates['rates'] as $code => $rate): ?>
                    <tr><td><?= e($code) ?></td><td><?= e(number_format($rate, 5)) ?></td></tr>
                <?php endforeach ?>
            </table>
            <p class="muted small">ECB reference date <?= e($rates['date']) ?><?= $rates['stale'] ? ', served from cache (API down)' : '' ?>.</p>
        <?php endif ?>
    </section>
</div>
