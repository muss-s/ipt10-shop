<h1>Checkout</h1>
<div class="two-col">
    <form method="post" action="<?= url('/checkout') ?>" class="form">
        <?= csrf_field() ?>
        <label>Full name
            <input name="shipping_name" value="<?= e(old('shipping_name', $user['name'])) ?>" required>
            <?php if ($err = error_for('shipping_name')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <label>Shipping address
            <textarea name="shipping_address" rows="3" required><?= e(old('shipping_address')) ?></textarea>
            <?php if ($err = error_for('shipping_address')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <label>Phone
            <input name="phone" value="<?= e(old('phone')) ?>" required>
            <?php if ($err = error_for('phone')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <p class="muted small">Payment: cash on delivery.</p>
        <button class="btn" type="submit">Place order (<?= peso($cart['total_cents']) ?>)</button>
    </form>

    <aside class="summary">
        <h3>Order summary</h3>
        <?php foreach ($cart['lines'] as $line): ?>
            <div class="row between"><span><?= e($line['product']['name']) ?> × <?= (int) $line['qty'] ?></span><span><?= price($line['subtotal_cents']) ?></span></div>
        <?php endforeach ?>
        <hr>
        <div class="row between"><strong>Total</strong><strong><?= price($cart['total_cents']) ?></strong></div>
    </aside>
</div>
