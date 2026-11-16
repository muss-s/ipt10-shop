<?php

/**
 * Creates the tables and (with --seed) adds sample categories, products and test users.
 *   php bin/migrate.php            create tables
 *   php bin/migrate.php --fresh    delete the SQLite file first
 *   php bin/migrate.php --seed     also insert sample data
 */

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Core\Config;
use Core\Database;

$root = require dirname(__DIR__) . '/framework/bootstrap.php';
$args = array_slice($argv, 1);
$config = Config::get('database');

if (in_array('--fresh', $args, true) && $config['driver'] === 'sqlite' && is_file($config['database'])) {
    unlink($config['database']);
    echo "Deleted {$config['database']}\n";
}

$db = Database::connection();
$suffix = $config['driver'] === 'mysql' ? '.mysql.sql' : '.sql';

foreach (glob("$root/database/migrations/*.sql") as $file) {
    $isMysql = str_ends_with($file, '.mysql.sql');
    if ($isMysql !== ($suffix === '.mysql.sql')) {
        continue;
    }
    $db->pdo()->exec(file_get_contents($file));
    echo 'Migrated ' . basename($file) . "\n";
}

if (!in_array('--seed', $args, true)) {
    exit(0);
}

if (User::count() > 0) {
    echo "Data already present, skipping seed.\n";
    exit(0);
}

User::register('Store Admin', Config::env('SEED_ADMIN_EMAIL', 'admin@shop.test'), Config::env('SEED_ADMIN_PASSWORD', 'admin123'), 'admin');
User::register('Juan Dela Cruz', Config::env('SEED_CUSTOMER_EMAIL', 'juan@shop.test'), Config::env('SEED_CUSTOMER_PASSWORD', 'customer123'));

$categories = [];
foreach (['Tops', 'Bottoms', 'Outerwear', 'Accessories'] as $name) {
    $categories[$name] = Category::create(['name' => $name, 'slug' => Product::slugify($name)]);
}

// Japanese street style catalog. Products have no image URL, so the store
// draws a poster for each one (see product_art() in app/helpers.php).
$products = [
    ['Tops', 'Kage Kanji Oversized Tee', 'Heavyweight 240gsm cotton tee with a cracked 影 print across the back. Boxy, dropped-shoulder fit.', 1290.00, 40],
    ['Tops', 'Koi Wave Long Sleeve', 'Long sleeve tee with a koi and wave print running down both arms. Garment dyed charcoal.', 1590.00, 18],
    ['Tops', 'Shibuya Midnight Hoodie', 'Brushed fleece hoodie with a reflective neon crossing graphic that lights up under flash.', 2890.00, 4],
    ['Bottoms', 'Ronin Cargo Tech Pants', 'Ripstop cargo pants with six pockets, bungee hems and an adjustable webbing belt.', 2490.00, 20],
    ['Bottoms', 'Wide Hakama Trousers', 'Ultra-wide pleated trousers inspired by the hakama, cut in a drapey black twill.', 2790.00, 12],
    ['Outerwear', 'Tora Sukajan Jacket', 'Reversible satin souvenir jacket with an embroidered tiger on the back and a plain black reverse.', 4990.00, 6],
    ['Outerwear', 'Yoru Haori Overshirt', 'Kimono-sleeve haori overshirt in indigo-dyed cotton. Wear it open over a tee.', 3490.00, 3],
    ['Accessories', 'Oni Bucket Hat', 'Washed canvas bucket hat with an embroidered oni mask on the front.', 890.00, 30],
    ['Accessories', 'Tabi Split-Toe Socks', 'Pack of three split-toe socks in black, white and red. One size.', 490.00, 50],
];

foreach ($products as $i => [$cat, $name, $desc, $price, $stock]) {
    Product::create([
        'category_id' => $categories[$cat],
        'name' => $name,
        'slug' => Product::slugify($name),
        'description' => $desc,
        'price_cents' => (int) round($price * 100),
        'stock' => $stock,
        'image_url' => '',
    ]);
}

echo "Seeded 2 users, " . count($categories) . " categories, " . count($products) . " products.\n";
