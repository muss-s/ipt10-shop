<?php

namespace App\Models;

use Core\Model;

class Product extends Model
{
    protected static string $table = 'products';
    protected static array $fillable = ['category_id', 'name', 'slug', 'description', 'price_cents', 'stock', 'image_url'];

    /** Catalog list with optional search text and category slug. */
    public static function search(?string $term = null, ?string $category = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE 1 = 1';
        $params = [];
        if ($term) {
            $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
            $params[] = "%$term%";
            $params[] = "%$term%";
        }
        if ($category) {
            $sql .= ' AND c.slug = ?';
            $params[] = $category;
        }
        return self::db()->fetchAll($sql . ' ORDER BY p.created_at DESC, p.id DESC', $params);
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::db()->fetch(
            'SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ?',
            [$slug],
        );
    }

    public static function findMany(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows = self::db()->fetchAll("SELECT * FROM products WHERE id IN ($marks)", array_values($ids));
        return array_column($rows, null, 'id');
    }

    public static function latest(int $limit = 4): array
    {
        return self::db()->fetchAll('SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit);
    }

    public static function slugify(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
        return $slug !== '' ? $slug : 'product';
    }

    /** A slug not used by any other product. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = self::slugify($name);
        $slug = $base;
        $n = 2;
        while (($row = self::findBy('slug', $slug)) && (int) $row['id'] !== $ignoreId) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
