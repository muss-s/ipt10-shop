<?php

namespace Core;

use PDO;
use PDOStatement;

/**
 * One shared PDO connection (SQLite or MySQL) with small query helpers.
 * Every query uses prepared statements, so user input is never put into SQL text.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    public function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $config['database'];
            $this->pdo = new PDO($dsn);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] ?? 3306,
                $config['database'],
            );
            $this->pdo = new PDO($dsn, $config['username'], $config['password']);
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public static function connection(): self
    {
        return self::$instance ??= new self(Config::get('database'));
    }

    public static function setConnection(?Database $db): void
    {
        self::$instance = $db;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        return $this->query($sql, $params)->fetch() ?: null;
    }

    public function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO $table ($cols) VALUES ($marks)", array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
        return $this->query("UPDATE $table SET $set WHERE $where", [...array_values($data), ...$whereParams])->rowCount();
    }

    /**
     * Runs $callback inside a transaction; rolls back if it throws.
     */
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
