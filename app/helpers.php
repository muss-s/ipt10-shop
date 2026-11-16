<?php

use App\Services\Cart;
use App\Services\CurrencyService;

/** Shared currency service for templates. */
function currency(): CurrencyService
{
    static $service = null;
    return $service ??= new CurrencyService();
}

/** Price in the shopper's chosen currency (base prices are stored in pesos). */
function price(int $cents): string
{
    return currency()->format($cents);
}

function cart_count(): int
{
    return Cart::count();
}

/** Kanji + Japanese label for each category slug. */
function category_kanji(?string $slug): array
{
    return [
        'tops' => ['上', 'トップス'],
        'bottoms' => ['下', 'ボトムス'],
        'outerwear' => ['外', 'アウター'],
        'accessories' => ['飾', 'アクセ'],
    ][$slug] ?? ['服', 'アイテム'];
}

/**
 * Product image. Uses image_url when set; otherwise draws a poster as inline SVG
 * with a big kanji and colors picked from the product's slug.
 */
function product_art(array $product, string $class = ''): string
{
    if (!empty($product['image_url'])) {
        return '<img class="' . e($class) . '" src="' . e($product['image_url']) . '" alt="' . e($product['name']) . '" loading="lazy">';
    }

    $kanji = [
        'kage-kanji-oversized-tee' => ['影', 'カゲ'],
        'koi-wave-long-sleeve' => ['鯉', 'コイ'],
        'shibuya-midnight-hoodie' => ['夜', 'シブヤ'],
        'ronin-cargo-tech-pants' => ['浪', 'ローニン'],
        'wide-hakama-trousers' => ['袴', 'ハカマ'],
        'tora-sukajan-jacket' => ['虎', 'トラ'],
        'yoru-haori-overshirt' => ['羽', 'ハオリ'],
        'oni-bucket-hat' => ['鬼', 'オニ'],
        'tabi-split-toe-socks' => ['足', 'タビ'],
    ];
    $fallback = ['龍', '雷', '桜', '狐', '侍', '波', '火', '風'];
    $hash = crc32($product['slug'] ?? $product['name']);
    [$big, $kana] = $kanji[$product['slug'] ?? ''] ?? [$fallback[$hash % count($fallback)], 'ストリート'];

    // [background, sun, kanji, accent]
    $palettes = [
        ['#111114', '#ff2a3d', '#f2efe6', '#ffe14d'],
        ['#ff2a3d', '#111114', '#f2efe6', '#ffe14d'],
        ['#f2efe6', '#ff2a3d', '#111114', '#111114'],
        ['#1b1035', '#3df2ff', '#f2efe6', '#ff2a3d'],
        ['#ffe14d', '#ff2a3d', '#111114', '#111114'],
        ['#0d2b2a', '#ffe14d', '#f2efe6', '#3df2ff'],
    ];
    [$bg, $sun, $ink, $accent] = $palettes[$hash % count($palettes)];
    $id = 'p' . dechex($hash);
    $name = e(mb_strtoupper($product['name']));
    $class = e($class);
    $no = str_pad((string) ($product['id'] ?? 0), 3, '0', STR_PAD_LEFT);

    return <<<SVG
<svg class="art {$class}" viewBox="0 0 400 300" role="img" aria-label="{$name}" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
  <defs>
    <pattern id="{$id}s" width="14" height="14" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
      <rect width="3" height="14" fill="{$ink}" opacity=".07"/>
    </pattern>
  </defs>
  <rect width="400" height="300" fill="{$bg}"/>
  <rect width="400" height="300" fill="url(#{$id}s)"/>
  <circle class="art-sun" cx="270" cy="130" r="92" fill="{$sun}"/>
  <text class="art-kanji" x="200" y="205" text-anchor="middle" font-size="190" fill="{$ink}" font-family="'Dela Gothic One', sans-serif">{$big}</text>
  <text x="372" y="34" font-size="20" fill="{$accent}" font-family="'Dela Gothic One', sans-serif" style="writing-mode: vertical-rl" letter-spacing="4">{$kana}</text>
  <rect x="18" y="246" width="230" height="34" fill="{$ink}"/>
  <text x="28" y="268" font-size="12" fill="{$bg}" font-family="'Space Mono', monospace" letter-spacing="1">{$name}</text>
  <text x="22" y="34" font-size="12" fill="{$ink}" font-family="'Space Mono', monospace" opacity=".7">NO.{$no} / 影 SUPPLY</text>
</svg>
SVG;
}
