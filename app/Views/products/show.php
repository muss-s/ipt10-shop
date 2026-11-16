<a class="back" href="<?= url('/products') ?>">← 戻る / All items</a>
<div class="detail">
    <div class="detail-media" data-tilt data-reveal>
        <?= product_art($product, 'detail-art') ?>
        <span class="card-glare"></span>
        <?php if ($product['stock'] <= 0): ?><span class="hanko hanko-big">完売<small>SOLD OUT</small></span><?php endif ?>
    </div>
    <div class="detail-info">
        <p class="eyebrow" data-reveal>NO.<?= str_pad((string) $product['id'], 3, '0', STR_PAD_LEFT) ?> / <?= e($product['category_name'] ?? 'Uncategorized') ?></p>
        <h1 class="detail-title" data-reveal style="--d:80ms"><?= e($product['name']) ?></h1>
        <p class="price" data-reveal style="--d:160ms"><?= price((int) $product['price_cents']) ?></p>
        <p class="detail-desc" data-reveal style="--d:220ms"><?= nl2br(e($product['description'])) ?></p>

        <p class="stock-line" data-reveal style="--d:260ms">
            <?php if ($product['stock'] > 5): ?><i class="dot ok"></i> In stock · 在庫あり
            <?php elseif ($product['stock'] > 0): ?><i class="dot low"></i> Only <?= (int) $product['stock'] ?> left · 残りわずか
            <?php else: ?><i class="dot out"></i> Sold out · 完売<?php endif ?>
        </p>

        <?php if ($product['stock'] > 0): ?>
            <form method="post" action="<?= url('/cart/add') ?>" class="buy" data-reveal style="--d:300ms">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="stepper">
                    <button type="button" data-step="-1" aria-label="Less">−</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" class="qty" aria-label="Quantity">
                    <button type="button" data-step="1" aria-label="More">+</button>
                </div>
                <button class="btn btn-big btn-add" type="submit" data-magnetic><span>Add to cart</span><span aria-hidden="true">カートに入れる</span></button>
            </form>
        <?php endif ?>

        <div class="fx-card" data-reveal style="--d:360ms">
            <h3>Price in other currencies <span>為替</span></h3>
            <div class="fx-grid">
                <?php foreach ($prices as $code => $formatted): ?>
                    <div class="<?= $code === currency()->current() ? 'active' : '' ?>"><small><?= e($code) ?></small><b><?= e($formatted) ?></b></div>
                <?php endforeach ?>
            </div>
            <?php if ($rates): ?>
                <p class="muted small">ECB rates from <?= e($rates['date']) ?><?= $rates['stale'] ? ' (cached)' : '' ?> via Frankfurter API.</p>
            <?php else: ?>
                <p class="muted small">Live rates are unavailable right now.</p>
            <?php endif ?>
        </div>
    </div>
</div>
