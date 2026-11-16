<h1>Your cart</h1>

<?php if (!$cart['lines']): ?>
    <p class="empty">Your cart is empty. <a href="<?= url('/products') ?>">Browse products</a>.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($cart['lines'] as $line): $p = $line['product']; ?>
            <tr>
                <td><a href="<?= url('/products/' . $p['slug']) ?>"><?= e($p['name']) ?></a></td>
                <td><?= price((int) $p['price_cents']) ?></td>
                <td>
                    <form method="post" action="<?= url('/cart/update') ?>" class="row">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                        <input type="number" name="quantity" value="<?= (int) $line['qty'] ?>" min="0" max="<?= (int) $p['stock'] ?>" class="qty">
                        <button class="btn btn-small btn-ghost" type="submit">Update</button>
                    </form>
                </td>
                <td><?= price($line['subtotal_cents']) ?></td>
                <td>
                    <form method="post" action="<?= url('/cart/remove') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                        <button class="linklike danger" type="submit">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot><tr><th colspan="3">Total</th><th colspan="2"><?= price($cart['total_cents']) ?></th></tr></tfoot>
    </table>
    <?php if (currency()->current() !== config('app.base_currency')): ?>
        <p class="muted small">You will be charged <?= peso($cart['total_cents']) ?>. Other currencies are estimates.</p>
    <?php endif ?>
    <a class="btn" href="<?= url('/checkout') ?>">Proceed to checkout</a>
<?php endif ?>
