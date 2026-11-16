<?php

use Core\Session;

$user = auth_user();
$success = Session::getFlash('success');
$error = Session::getFlash('error');
$rates = currency()->rates();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isActive = fn (string $prefix) => str_starts_with($path, url($prefix)) ? 'is-active' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') . ' · ' . config('app.name')) ?></title>
    <script>
        // Runs before paint: enables JS-only styles and the first-visit intro.
        document.documentElement.classList.add('js');
        try { if (!sessionStorage.getItem('kage-intro')) document.documentElement.classList.add('show-intro'); } catch (e) {}
        try { if (sessionStorage.getItem('kage-wipe')) { document.documentElement.classList.add('wipe-enter'); sessionStorage.removeItem('kage-wipe'); } } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Space+Mono:wght@400;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/css/app.css') ?>">
</head>
<body>

<!-- First-visit intro -->
<div id="intro" aria-hidden="true">
    <div class="intro-kanji">影</div>
    <div class="intro-slash"></div>
    <div class="intro-meta"><span>KAGE SUPPLY</span><span>東京 — EST. 2026</span></div>
    <div class="intro-bar"><i></i></div>
</div>

<!-- Page transition panel -->
<div class="wipe" aria-hidden="true"><span>影</span></div>

<div class="grain" aria-hidden="true"></div>

<div class="ticker" aria-hidden="true">
    <div class="ticker-track">
        <?php for ($i = 0; $i < 2; $i++): ?>
            <span>NEW DROP 新作</span><span>★</span><span>FREE SHIPPING OVER ₱3,000 送料無料</span><span>★</span>
            <span>TOKYO STREET STYLE 東京</span><span>★</span><span>PRICED IN ₱ $ € ¥ S$</span><span>★</span>
            <span>LIMITED STOCK 限定</span><span>★</span>
        <?php endfor ?>
    </div>
</div>

<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="<?= url('/') ?>" data-magnetic>
            <span class="brand-kanji">影</span>
            <span class="brand-text">KAGE<small>SUPPLY</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Menu" aria-expanded="false"><span></span><span></span></button>
        <nav class="nav">
            <a class="<?= $isActive('/products') ?>" href="<?= url('/products') ?>" data-jp="商品">Shop</a>
            <a class="<?= $isActive('/cart') ?>" href="<?= url('/cart') ?>" data-jp="カート">Cart<?php if (cart_count()): ?> <span class="badge <?= $success ? 'bump' : '' ?>"><?= cart_count() ?></span><?php endif ?></a>
            <?php if ($user): ?>
                <a class="<?= $isActive('/orders') ?>" href="<?= url('/orders') ?>" data-jp="注文">Orders</a>
                <?php if ($user['role'] === 'admin'): ?><a class="<?= $isActive('/admin') ?>" href="<?= url('/admin') ?>" data-jp="管理">Admin</a><?php endif ?>
                <form method="post" action="<?= url('/logout') ?>" class="inline">
                    <?= csrf_field() ?>
                    <button class="linklike nav-out" type="submit">Log out <span class="muted">/ <?= e($user['name']) ?></span></button>
                </form>
            <?php else: ?>
                <a class="<?= $isActive('/login') ?>" href="<?= url('/login') ?>" data-jp="ログイン">Log in</a>
                <a href="<?= url('/register') ?>" class="btn btn-small" data-magnetic>Join 入会</a>
            <?php endif ?>
        </nav>
        <div class="currency" title="Display currency (live exchange rates)">
            <?php foreach (currency()->supported() as $code): ?>
                <?php if ($code === config('app.base_currency') || $rates): ?>
                    <a href="<?= url('/currency/' . $code) ?>" class="<?= $code === currency()->current() ? 'active' : '' ?>"><?= $code ?></a>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    </div>
</header>

<div class="toasts">
    <?php if ($success): ?><div class="toast toast-success" role="status"><b>OK!</b> <?= e($success) ?></div><?php endif ?>
    <?php if ($error): ?><div class="toast toast-error" role="alert"><b>注意</b> <?= e($error) ?></div><?php endif ?>
</div>

<main class="wrap page">
    <?= $content ?>
</main>

<footer class="footer">
    <div class="footer-big" aria-hidden="true">影 KAGE SUPPLY 影 KAGE SUPPLY</div>
    <div class="wrap footer-row">
        <span>IPT10 Final Project · built on a custom PHP framework</span>
        <span>
            <?php if ($rates): ?>
                Rates: Frankfurter / ECB · <?= e($rates['date']) ?><?= $rates['stale'] ? ' (cached, API unreachable)' : '' ?>
            <?php else: ?>
                Exchange rates unavailable · prices in PHP
            <?php endif ?>
        </span>
    </div>
</footer>

<script src="<?= url('/js/app.js') ?>" defer></script>
</body>
</html>
