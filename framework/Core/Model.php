<?php

namespace Framework\Core;

use Framework\Core\Database;
use PDO;

/**
 * Class Model
 * 
 * Base class for all Models in the framework.
 * Inspired by Laravel Eloquent ORM.
 * 
 * Provides:
 * - CRUD operations
 * - Fluent query builder (where, orderBy, limit, joins)
 * - Transactions
 * - Soft deletes
 * - Automatic timestamps
 * - Aggregates (count, sum, avg, min, max)
 * - Pagination
 * - Scopes
 * - Event hooks (creating, updating, etc.)
 * - Concurrency control (lockForUpdate)
 * 
 * @package Framework\Core
 */
abstract class Model
{
    /** @var string Table name (must be defined in child class) */
    protected static string $table;

    /** @var array Fillable fields (mass assignment protection) */
    protected array $fillable = [];

    /** @var bool Automatically handle created_at and updated_at timestamps */
    protected static bool $timestamps = true;

    /** @var bool Enable soft deletes (deleted_at column) */
    protected static bool $softDelete = true;

    /** @var array Query builder: WHERE conditions */
    protected array $wheres = [];

    /** @var array Query builder: bindings */
    protected array $bindings = [];

    /** @var array Query builder: JOIN clauses */
    protected array $joins = [];

    /** @var string|null ORDER BY clause */
    protected ?string $orderBy = null;

    /** @var int|null LIMIT clause */
    protected ?int $limit = null;

    /** @var int|null OFFSET clause (for pagination) */
    protected ?int $offset = null;

    /** @var array Registered event callbacks */
    protected static array $events = [
        'creating' => [],
        'created' => [],
        'updating' => [],
        'updated' => [],
        'deleting' => [],
        'deleted' => [],
        'restoring' => [],
        'restored' => [],
    ];

    /**
     * Get PDO instance
     *
     * @return PDO
     */
    protected static function db(): PDO
    {
        return Database::getConnection();
    }

    /**
     * Run an event callback.
     */
    protected static function fireEvent(string $event, array $payload = []): void
    {
        if (!empty(self::$events[$event])) {
            foreach (self::$events[$event] as $callback) {
                $callback($payload);
            }
        }
    }

    /**
     * Add a WHERE condition (fluent).
     *
     * @param string $column Column name
     * @param string $operator SQL operator (=, <, >, LIKE, etc.)
     * @param mixed $value Value to compare
     * @return $this
     * 
     * Example:
     * $users = (new User())->where('name', '=', 'Jonas')->get();
     */
    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = "`$column` $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add a WHERE condition with LIKE operator
     *
     * @param string $column Column name
     * @param string $value Value to match
     * @return $this
     *
     * Example:
     * $users = (new User())->like('name', 'Jonas')->get();
     */
    public function like(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', "%{$value}%");
    }

    /**
     * Add a WHERE condition for strings that start with value
     *
     * @param string $column
     * @param string $value
     * @return $this
     *
     * Example:
     * $users = (new User())->startsWith('name', 'Jon')->get();
     */
    public function startsWith(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', "{$value}%");
    }

    /**
     * Add a WHERE condition for strings that end with value
     *
     * @param string $column
     * @param string $value
     * @return $this
     *
     * Example:
     * $users = (new User())->endsWith('name', 'nas')->get();
     */
    public function endsWith(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', "%{$value}");
    }

    /**
     * Add a WHERE IN condition
     *
     * @param string $column
     * @param array $values
     * @return $this
     *
     * Example:
     * $users = (new User())->whereIn('id', [1,2,3])->get();
     */
    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "`$column` IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Add a WHERE NOT IN condition
     *
     * @param string $column
     * @param array $values
     * @return $this
     *
     * Example:
     * $users = (new User())->whereNotIn('id', [4,5])->get();
     */
    public function whereNotIn(string $column, array $values): self
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "`$column` NOT IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Add a WHERE column IS NULL
     *
     * @param string $column
     * @return $this
     *
     * Example:
     * $users = (new User())->whereNull('deleted_at')->get();
     */
    public function whereNull(string $column): self
    {
        $this->wheres[] = "`$column` IS NULL";
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL
     *
     * @param string $column
     * @return $this
     *
     * Example:
     * $users = (new User())->whereNotNull('deleted_at')->get();
     */
    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "`$column` IS NOT NULL";
        return $this;
    }

    /**
     * Add a OR WHERE condition
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     *
     * Example:
     * $users = (new User())->where('role','admin')->orWhere('role','manager')->get();
     */
    public function orWhere(string $column, string $operator, $value): self
    {
        $lastWhere = array_pop($this->wheres);
        $lastBinding = array_pop($this->bindings);
        $this->wheres[] = "($lastWhere OR `$column` $operator ?)";
        $this->bindings[] = $lastBinding;
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add a JOIN clause (fluent).
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator SQL operator
     * @param string $second Second column
     * @param string $type JOIN type (INNER, LEFT, RIGHT)
     * @return $this
     * 
     * Example:
     * $users = (new User())->join('profiles', 'users.id', '=', 'profiles.user_id')->get();
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "$type JOIN `$table` ON `$first` $operator `$second`";
        return $this;
    }

    /**
     * Add an ORDER BY clause (fluent).
     *
     * @param string $column Column name
     * @param string $direction ASC or DESC
     * @return $this
     * 
     * Example:
     * $users = (new User())->orderBy('id', 'DESC')->get();
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "`$column` $direction";
        return $this;
    }

    /**
     * Add a LIMIT clause (fluent).
     *
     * @param int $limit Number of records to return
     * @return $this
     * 
     * Example:
     * $users = (new User())->limit(5)->get();
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add an OFFSET clause (fluent).
     *
     * @param int $offset Number of records to skip
     * @return $this
     * 
     * Example:
     * $users = (new User())->offset(10)->limit(5)->get();
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute the built query and return results.
     *
     * @return array
     * 
     *  Example:
     * $users = (new User())
     *     ->where('email', 'LIKE', '%@example.com')
     *     ->orderBy('id', 'DESC')
     *     ->limit(10)
     *     ->get();
     */
    public function get(): array
    {
        $stmt = self::db()->prepare($this->buildQuery());
        $stmt->execute($this->bindings);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->resetQuery();
        return $results;
    }

    /**
     * Get the first result from the built query.
     *
     * @return array|null
     * 
     * Example:
     * $user = (new User())->where('email', '=', 'jonas@example.com')->first();
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Retrieve the first record matching attributes or create it if none exists
     *
     * @param array $attributes Attributes to match
     * @param array $values Values to set if creating
     * @return array
     *
     * Example:
     * $user = User::firstOrCreate(
     *     ['email' => 'jonas@example.com'],
     *     ['name' => 'Jonas Silva']
     * );
     */
    public static function firstOrCreate(array $attributes, array $values = []): array
    {
        $query = new static();

        foreach ($attributes as $column => $value) {
            $query->where($column, '=', $value);
        }

        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        return self::create(array_merge($attributes, $values));
    }

    /**
     * Lock a row for update (concurrency control).
     *
     * @param string $column Column name
     * @param string $operator SQL operator
     * @param mixed $value Value to filter
     * @return array|null
     * 
     * Example:
     * $user = (new User())->lockForUpdate('id', '=', 1);
     */
    public function lockForUpdate(string $column, string $operator, $value): ?array
    {
        $pdo = self::db();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            "SELECT * FROM `" . static::$table . "` WHERE `$column` $operator ? FOR UPDATE"
        );
        $stmt->execute([$value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        return (new static())->get();
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
    public static function find(int $id): ?array
    {
        return (new static())->where('id', '=', $id)->first();
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return array Created record
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
        self::fireEvent('creating', $data);
        if (static::$timestamps) {
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $fields = array_intersect(array_keys($data), $instance->fillable);
        $columns = '`' . implode('`,`', $fields) . '`';
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $values = array_map(fn($f) => $data[$f], $fields);


        $stmt = self::db()->prepare("INSERT INTO `" . static::$table . "` ($columns) VALUES ($placeholders)");
        $stmt->execute($values);
        $id = (int)self::db()->lastInsertId();
        $new = array_merge(['id' => $id], $data);


        self::fireEvent('created', $new);
        return $new;
    }

    /**
     * Create or update a record (idempotent operation)
     *
     * @param array $data
     * @param string $uniqueColumn Column to check for existing record
     * @return array
     * 
     * Example:
     * $user = User::createOrUpdate(['email' => 'jonas@example.com', 'name' => 'Jonas'], 'email');
     */
    public static function createOrUpdate(array $data, string $uniqueColumn): array
    {
        $existing = self::where($uniqueColumn, '=', $data[$uniqueColumn]);
        if ($existing) {
            self::update($existing[0]['id'], $data);
            return array_merge(['id' => $existing[0]['id']], $data);
        }
        return self::create($data);
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
        self::fireEvent('updating', $data);

        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }


        $fields = [];
        $values = [];
        foreach ($instance->fillable as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = ?";
                $values[] = $data[$f];
            }
        }
        $values[] = $id;
        $sql = sprintf("UPDATE `%s` SET %s WHERE id = ?", static::$table, implode(', ', $fields));
        $stmt = self::db()->prepare($sql);
        $success = $stmt->execute($values);


        if ($success) self::fireEvent('updated', $data);
        return $success;
    }

    /**
     * Create or update a record by unique column (idempotent)
     *
     * @param array $attributes Attributes to match for uniqueness
     * @param array $values Values to insert/update
     * @return array
     *
     * Example:
     * $user = User::updateOrCreate(
     *     ['email' => 'jonas@example.com'],
     *     ['name' => 'Jonas Silva']
     * );
     */
    public static function updateOrCreate(array $attributes, array $values): array
    {
        $query = new static();

        foreach ($attributes as $column => $value) {
            $query->where($column, '=', $value);
        }
        $existing = $query->first();

        if ($existing) {
            self::update($existing['id'], array_merge($existing, $values));
            return array_merge(['id' => $existing['id']], $values);
        }

        return self::create(array_merge($attributes, $values));
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
        self::fireEvent('deleting', ['id' => $id]);

        if (static::$softDelete) {
            $success = self::update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
            if ($success) self::fireEvent('deleted', ['id' => $id]);
            return $success;
        }
        $stmt = self::db()->prepare("DELETE FROM `" . static::$table . "` WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) self::fireEvent('deleted', ['id' => $id]);

        return $success;
    }

    /**
     * Restore a soft-deleted record by ID.
     *
     * @param int $id
     * @return bool
     * 
     * Example:
     * User::restore(1);
     */
    public static function restore(int $id): bool
    {
        self::fireEvent('restoring', ['id' => $id]);
        $success = self::update($id, ['deleted_at' => null]);

        if ($success) self::fireEvent('restored', ['id' => $id]);

        return $success;
    }

    /**
     * Permanently delete a record by ID (bypass soft delete)
     *
     * @param int $id
     * @return bool
     * 
     * Example:
     * User::forceDelete(1);
     */
    public static function forceDelete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM `" . static::$table . "` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Include soft-deleted records in query results
     *
     * @return $this
     * 
     * Example:
     * $users = (new User())->withTrashed()->get();
     */
    public function withTrashed(): self
    {
        $this->wheres = array_filter(
            $this->wheres,
            fn($w) => stripos($w, 'deleted_at') === false
        );

        return $this;
    }

    /**
     * Only retrieve soft-deleted records
     *
     * @return $this
     * 
     * Example:
     * $deletedUsers = (new User())->onlyTrashed()->get();
     */
    public function onlyTrashed(): self
    {
        $this->wheres = array_filter(
            $this->wheres,
            fn($w) => stripos($w, 'deleted_at') === false
        );

        $this->wheres[] = "`deleted_at` IS NOT NULL";

        return $this;
    }

    /**
     * Build the SQL query string from the current state.
     *
     * @return string
     * 
     * Example:
     * $sql = (new User())->where('name', '=', 'Jonas')->buildQuery();
     */
    protected function buildQuery(): string
    {
        $sql = 'SELECT * FROM `' . static::$table . '`';
        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        $wheres = $this->wheres;
        if (static::$softDelete && !array_filter($wheres, fn($w) => stripos($w, 'deleted_at') !== false)) {
            $wheres[] = '`deleted_at` IS NULL';
        }
        if ($wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        return $sql;
    }

    /**
     * Count total records in the table.
     *
     * @return int
     * 
     * Example:
     * $totalUsers = User::count();
     */
    public function count(): int
    {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM `" . static::$table . "`");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Sum a column's values across all records.
     *
     * @param string $column
     * @return float
     * 
     * Example:
     * $totalSales = Order::sum('amount');
     */
    public function sum(string $column): float
    {
        $stmt = self::db()->query("SELECT SUM(`$column`) AS total FROM `" . static::$table . "`");
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calculate the average of a column's values.
     *
     * @param string $column
     * @return float
     * 
     * Example:
     * $averageAge = User::avg('age');
     */
    public function avg(string $column): float
    {
        $stmt = self::db()->query("SELECT AVG(`$column`) AS avg FROM `" . static::$table . "`");
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['avg'];
    }

    /**
     * Get the minimum value of a column.
     * 
     * @param string $column
     * @return float
     * 
     * Example:
     * $minPrice = Product::min('price');
     */
    public function min(string $column): float
    {
        $stmt = self::db()->query("SELECT MIN(`$column`) AS min FROM `" . static::$table . "`");
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['min'];
    }

    /**
     * Get the maximum value of a column.
     *
     * @param string $column
     * @return float
     * 
     * Example:
     * $maxScore = Game::max('score');
     */
    public function max(string $column): float
    {
        $stmt = self::db()->query("SELECT MAX(`$column`) AS max FROM `" . static::$table . "`");
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['max'];
    }

    /**
     * Paginate results.
     *
     * @param int $perPage Number of records per page
     * @param int $page Current page number
     * @return array
     * 
     * Example:
     * $pagination = (new User())->paginate(10, 2);
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        $this->limit($perPage)->offset($offset);
        $results = $this->get();
        $total = $this->count();
        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ];
    }

    /**
     * Start a transaction
     *
     * @return void
     * 
     * Example:
     * User::beginTransaction();
     */
    public static function beginTransaction(): void
    {
        self::db()->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return void
     * 
     * Example:
     * User::commit();
     */
    public static function commit(): void
    {
        self::db()->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return void
     * 
     * Example:
     * User::rollback();
     */
    public static function rollback(): void
    {
        self::db()->rollBack();
    }

    /**
     * Reset fluent query builder internal state
     *
     * @return void
     */
    protected function resetQuery(): void
    {
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = null;
        $this->limit = null;
        $this->offset = null;
        $this->joins = [];
    }

    /**
     * Register an event callback
     *
     * @param string $event Event name (creating, created, updating, updated, deleting, deleted)
     * @param callable $callback Callback function
     * @return void
     * 
     * Example:
     * User::on('creating', function($data) {
     *     // Do something before creating a user
     * });
     */
    public static function on(string $event, callable $callback): void
    {
        self::$events[$event][] = $callback;
    }

    /**
     * Get the raw SQL query string for debugging.
     *
     * @return string
     * 
     * Example:
     * $sql = (new User())->where('name', '=', 'Jonas')->toSql();
     */
    public function toSql(): string
    {
        return $this->buildQuery();
    }
}
