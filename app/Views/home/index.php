<?php use Core\View; ?>
<section class="hero" data-parallax>
    <div class="hero-vertical" data-depth="0.6" aria-hidden="true">ストリート東京</div>
    <div class="hero-sun" data-depth="0.25" aria-hidden="true"></div>
    <div class="hero-kanji" data-depth="0.4" aria-hidden="true">影</div>

    <div class="hero-copy">
        <p class="eyebrow" data-reveal>// FW26 COLLECTION — 秋冬</p>
        <h1 class="hero-title">
            <span class="line" data-reveal style="--d:80ms"><span class="glitch" data-text="WEAR THE">WEAR THE</span></span>
            <span class="line" data-reveal style="--d:180ms"><span class="outline">CITY</span> <span class="red">AFTER</span></span>
            <span class="line" data-reveal style="--d:280ms"><span class="glitch" data-text="DARK.">DARK.</span></span>
        </h1>
        <p class="hero-sub" data-reveal style="--d:380ms">Tokyo street style: oversized tees, cargo tech, sukajan and haori. Prices in yen, dollars, euros or pesos, with live exchange rates.</p>
        <div class="hero-cta" data-reveal style="--d:480ms">
            <a class="btn btn-big" href="<?= url('/products') ?>" data-magnetic>Shop the drop <span aria-hidden="true">→</span></a>
            <a class="btn btn-ghost btn-big" href="<?= url('/currency/JPY') ?>" data-magnetic>See prices in ¥</a>
        </div>
    </div>

    <svg class="spin-badge" viewBox="0 0 200 200" aria-hidden="true">
        <defs><path id="circle" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0"/></defs>
        <text><textPath href="#circle">NEW DROP • 新作 • LIMITED • 限定 • NEW DROP • 新作 •</textPath></text>
        <text x="100" y="118" text-anchor="middle" class="spin-badge-kanji">新</text>
    </svg>
</section>

<section class="marquee-big" aria-hidden="true">
    <div class="marquee-track">
        <?php for ($i = 0; $i < 2; $i++): ?>
            <span>原宿</span><span class="outline">HARAJUKU</span><span>渋谷</span><span class="outline">SHIBUYA</span><span>新宿</span><span class="outline">SHINJUKU</span>
        <?php endfor ?>
    </div>
</section>

<section class="section">
    <header class="section-head" data-reveal>
        <span class="section-kanji">類</span>
        <div><p class="eyebrow">01 / カテゴリー</p><h2>Categories</h2></div>
    </header>
    <div class="cats">
        <?php foreach ($categories as $n => $cat): [$k, $jp] = category_kanji($cat['slug']); ?>
            <a class="cat" href="<?= url('/products?category=' . urlencode($cat['slug'])) ?>" data-reveal style="--d: <?= $n * 90 ?>ms">
                <span class="cat-kanji"><?= $k ?></span>
                <span class="cat-name"><?= e($cat['name']) ?></span>
                <span class="cat-jp"><?= $jp ?></span>
                <span class="cat-arrow">→</span>
            </a>
        <?php endforeach ?>
    </div>
</section>

<section class="section">
    <header class="section-head" data-reveal>
        <span class="section-kanji">新</span>
        <div><p class="eyebrow">02 / 新作</p><h2>New arrivals</h2></div>
        <a class="section-link" href="<?= url('/products') ?>">View all →</a>
    </header>
    <div class="grid">
        <?php foreach ($products as $i => $product): ?>
            <?= View::partial('partials/product-card', ['product' => $product, 'i' => $i]) ?>
        <?php endforeach ?>
    </div>
</section>

<section class="fx-band" data-reveal>
    <div class="fx-symbols" aria-hidden="true"><span>¥</span><span>$</span><span>€</span><span>₱</span><span>S$</span></div>
    <div class="fx-copy">
        <p class="eyebrow">03 / 為替</p>
        <h2>Shop in any currency</h2>
        <p>Pick a currency at the top. Prices are converted with that day's European Central Bank rates from the Frankfurter API.</p>
        <?php if ($rates = currency()->rates()): ?>
            <div class="fx-rates">
                <?php foreach ($rates['rates'] as $code => $rate): ?>
                    <span><b><?= e($code) ?></b> <?= e(number_format($rate * 100, 2)) ?><small> / ₱100</small></span>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>
