<?php use Core\View; ?>
<header class="page-head">
    <p class="eyebrow" data-reveal>// 全商品 — <?= count($products) ?> ITEMS</p>
    <h1 class="page-title" data-reveal style="--d:100ms"><span class="glitch" data-text="ALL ITEMS">ALL ITEMS</span></h1>
    <span class="page-kanji" aria-hidden="true">店</span>
</header>

<form class="filters" method="get" action="<?= url('/products') ?>" data-reveal style="--d:180ms">
    <div class="search">
        <span aria-hidden="true">検索</span>
        <input type="search" name="q" value="<?= e($term ?? '') ?>" placeholder="Search hoodies, cargo, sukajan…">
    </div>
    <?php if (!empty($activeCategory)): ?><input type="hidden" name="category" value="<?= e($activeCategory) ?>"><?php endif ?>
    <button class="btn" type="submit">Search</button>
</form>

<nav class="chips" data-reveal style="--d:240ms">
    <a class="chip <?= empty($activeCategory) ? 'active' : '' ?>" href="<?= url('/products' . ($term ? '?q=' . urlencode($term) : '')) ?>">All 全部</a>
    <?php foreach ($categories as $cat): [$k] = category_kanji($cat['slug']); ?>
        <a class="chip <?= $activeCategory === $cat['slug'] ? 'active' : '' ?>" href="<?= url('/products?category=' . urlencode($cat['slug']) . ($term ? '&q=' . urlencode($term) : '')) ?>"><?= $k ?> <?= e($cat['name']) ?></a>
    <?php endforeach ?>
</nav>

<?php if (!$products): ?>
    <div class="empty" data-reveal><span class="empty-kanji">無</span>No products match your search.</div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($products as $i => $product): ?>
            <?= View::partial('partials/product-card', ['product' => $product, 'i' => $i]) ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
