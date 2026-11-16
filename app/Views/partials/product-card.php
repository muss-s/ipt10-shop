<?php $i = $i ?? 0; ?>
<article class="card" data-tilt data-reveal style="--d: <?= ($i % 4) * 90 ?>ms">
    <a class="card-media" href="<?= url('/products/' . $product['slug']) ?>">
        <?= product_art($product) ?>
        <span class="card-glare"></span>
        <?php if ($product['stock'] <= 0): ?>
            <span class="hanko">完売<small>SOLD OUT</small></span>
        <?php elseif ($product['stock'] <= 5): ?>
            <span class="sticker">残りわずか<small>ONLY <?= (int) $product['stock'] ?> LEFT</small></span>
        <?php endif ?>
    </a>
    <div class="card-body">
        <div class="card-meta">
            <span>NO.<?= str_pad((string) $product['id'], 3, '0', STR_PAD_LEFT) ?></span>
            <?php if (!empty($product['category_name'])): ?><span><?= e($product['category_name']) ?></span><?php endif ?>
        </div>
        <h3><a href="<?= url('/products/' . $product['slug']) ?>"><?= e($product['name']) ?></a></h3>
        <div class="card-foot">
            <strong class="card-price"><?= price((int) $product['price_cents']) ?></strong>
            <?php if ($product['stock'] > 0): ?>
                <form method="post" action="<?= url('/cart/add') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <button class="btn btn-small btn-add" type="submit"><span>Add +</span><span aria-hidden="true">カートへ</span></button>
                </form>
            <?php endif ?>
        </div>
    </div>
</article>
