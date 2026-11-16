<?php

namespace Core;

/**
 * Minimal table gateway. Subclasses set $table and $fillable.
 * Rows are returned as associative arrays to keep things simple.
 */
abstract class Model
{
    protected static string $table;
    protected static array $fillable = [];
    protected static bool $timestamps = true;

    protected static function db(): Database
    {
        return Database::connection();
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        return static::db()->fetchAll('SELECT * FROM ' . static::$table . " ORDER BY $orderBy");
    }

    public static function find(int $id): ?array
    {
        return static::db()->fetch('SELECT * FROM ' . static::$table . ' WHERE id = ?', [$id]);
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        static::assertColumn($column);
        return static::db()->fetch('SELECT * FROM ' . static::$table . " WHERE $column = ?", [$value]);
    }

    public static function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        static::assertColumn($column);
        return static::db()->fetchAll('SELECT * FROM ' . static::$table . " WHERE $column = ? ORDER BY $orderBy", [$value]);
    }

    public static function create(array $data): int
    {
        $data = static::filter($data);
        if (static::$timestamps) {
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return static::db()->insert(static::$table, $data);
    }

    public static function update(int $id, array $data): int
    {
        $data = static::filter($data);
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return static::db()->update(static::$table, $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        return static::db()->query('DELETE FROM ' . static::$table . ' WHERE id = ?', [$id])->rowCount();
    }

    public static function count(): int
    {
        return (int) static::db()->fetch('SELECT COUNT(*) AS n FROM ' . static::$table)['n'];
    }

    /** Drop keys that are not mass-assignable. */
    protected static function filter(array $data): array
    {
        return array_intersect_key($data, array_flip(static::$fillable));
    }

    /** Column names cannot be bound as parameters, so only allow known ones. */
    protected static function assertColumn(string $column): void
    {
        if (!in_array($column, [...static::$fillable, 'id'], true)) {
            throw new \InvalidArgumentException("Unknown column $column");
        }
    }
}
