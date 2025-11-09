<?php

namespace Framework\Core;

use Framework\Core\Database;
use PDO;

/**
 * Class Model
 * 
 * Base class for all Models in the framework.
 * It provides basic CRUD methods and simple queries.
 * 
 * @package Framework\Core
 */
abstract class Model
{
    /**
     * Table name in the database.
     * It must be defined in each child Model.
     *
     * @var string
     */
    protected static string $table;

    /**
     * Fields that can be filled in by the user.
     * It serves to protect against mass assignment.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * Returns the PDO connection.
     *
     * @return PDO
     */
    protected static function db(): PDO
    {
        return Database::getConnection();
    }

    /**
     * Returns all records from the table.
     *
     * @return array
     * 
     * Example:
     * $users = User::all();
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM " . static::$table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search for a record by ID.
     *
     * @param int $id
     * @return array|null
     * 
     * Example:
     * $user = User::find(1);
     */
    public static function find($id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return array
     * 
     * Example:
     * $user = User::create([
     *     'name' => 'Jonas',
     *     'email' => 'jonas@example.com'
     * ]);
     */
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

    /**
     * Updates an existing record by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * 
     * Example:
     * User::update(1, ['name' => 'Jonas Silva']);
     */
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

    /**
     * Delete a record by ID.
     *
     * @param int $id
     * @return bool
     * 
     * Example:
     * User::delete(1);
     */
    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM `" . static::$table . "` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search for records using a simple filter.
     *
     * @param string $column Table column
     * @param string $operator Operator SQL (ex: =, >, <, LIKE)
     * @param mixed $value Value to be compared
     * @return array
     * 
     * Example:
     * $users = User::where('name', 'LIKE', '%Jonas%');
     */
    public static function where(string $column, string $operator, $value): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM `" . static::$table . "` WHERE `$column` $operator ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
