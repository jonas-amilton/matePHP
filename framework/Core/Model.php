<?php

namespace Framework\Core;

use Framework\Core\Database;
use PDO;


abstract class Model
{
    protected static string $table;
    protected array $fillable = [];


    protected static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM " . static::$table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function create(array $data): array
    {
        $instance = new static();
        $fields = [];
        $placeholders = [];
        $values = [];
        foreach ($instance->fillable as $f) {
            if (isset($data[$f])) {
                $fields[] = "`$f`";
                $placeholders[] = '?';
                $values[] = $data[$f];
            }
        }
        $sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', static::$table, implode(',', $fields), implode(',', $placeholders));
        $stmt = self::db()->prepare($sql);
        $stmt->execute($values);
        $id = (int) self::db()->lastInsertId();
        return array_merge(['id' => $id], array_intersect_key($data, array_flip($instance->fillable)));
    }

    public static function update(int $id, array $data): bool
    {
        $instance = new static();
        $fields = [];
        $values = [];
        foreach ($instance->fillable as $f) {
            if (isset($data[$f])) {
                $fields[] = "`$f` = ?";
                $values[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $values[] = $id;
        $sql = sprintf('UPDATE `%s` SET %s WHERE id = ?', static::$table, implode(', ', $fields));
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM `" . static::$table . "` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function where(string $column, string $operator, $value): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM `" . static::$table . "` WHERE `$column` $operator ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
