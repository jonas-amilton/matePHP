<?php

namespace Framework\Core;

/**
 * Class Request
 *
 * Handles HTTP request data abstraction. Provides methods to retrieve
 * the request method, URI, and input data (from JSON or form submissions).
 *
 * Example usage:
 * ```php
 * $request = Request::capture();
 * $method = $request->method();     // e.g. 'GET'
 * $uri = $request->uri();           // e.g. '/users/1'
 * $name = $request->input('name');  // e.g. 'John'
 * $all = $request->all();           // returns all input data
 * ```
 *
 * @package Framework\Core
 */
class Request
{
    /** Sentinel value used when a key is not present in input payload */
    private const MISSING = '__MATEPHP_REQUEST_MISSING__';

    /** @var string|null Raw body cache */
    private ?string $rawBody = null;

    /** @var array<string,mixed>|null Decoded body cache */
    private ?array $decodedBody = null;

    /**
     * Capture the current HTTP request instance.
     *
     * Creates and returns a new Request object representing
     * the current PHP superglobals state.
     *
     * @return static
     */
    public static function capture(): self
    {
        return new static();
    }

    /**
     * Get the HTTP method of the current request.
     *
     * Defaults to "GET" if no method is set.
     *
     * @return string The HTTP method (GET, POST, PUT, PATCH, DELETE, etc.)
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get the URI path of the current request (without query string).
     *
     * Example:
     * ```
     * // For a request like: http://example.com/users?id=10
     * $request->uri(); // returns "/users"
     * ```
     *
     * @return string The request URI path
     */
    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return strtok($uri, '?') ?: '/';
    }

    /**
     * Retrieve query string values.
     *
     * Supports dot notation for nested arrays.
     *
     * @param string|null $key Query key, or null to return all query params
     * @param mixed $default Default value when key is not found
     * @return mixed
     */
    public function query(?string $key = null, $default = null)
    {
        $query = is_array($_GET ?? null) ? $_GET : [];

        if ($key === null) {
            return $query;
        }

        return $this->dataGet($query, $key, $default);
    }

    /**
     * Retrieve input data from request body and query string.
     *
     * Body values override query values when the same key exists.
     *
     * Examples:
     * ```php
     * $name = $request->input('name');
     * $all  = $request->input(); // all input data
     * ```
     *
     * @param string|null $key The input key to retrieve, or null to get all data
     * @param mixed $default Default value when key is not found
     * @return mixed The value of the input key, or an array of all input data
     */
    public function input(?string $key = null, $default = null)
    {
        $data = $this->all();

        if ($key === null) {
            return $data;
        }

        return $this->dataGet($data, $key, $default);
    }

    /**
     * Retrieve request body values only.
     *
     * @param string|null $key Body key, or null to return all body params
     * @param mixed $default Default value when key is not found
     * @return mixed
     */
    public function body(?string $key = null, $default = null)
    {
        $data = $this->decodedBody();

        if ($key === null) {
            return $data;
        }

        return $this->dataGet($data, $key, $default);
    }

    /**
     * Get all input data from the current request (query + body).
     *
     * Body values override query values.
     *
     * @return array<string, mixed> The full input data as an associative array
     */
    public function all(): array
    {
        $query = $this->query();
        $body = $this->body();
        return array_replace_recursive($query, $body);
    }

    /**
     * Check if an input key exists.
     *
     * @param string $key Input key (supports dot notation)
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->input($key, self::MISSING) !== self::MISSING;
    }

    /**
     * Check whether a key exists and is not empty.
     *
     * @param string $key Input key (supports dot notation)
     * @return bool
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key, self::MISSING);
        if ($value === self::MISSING || $value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    /**
     * Retrieve an integer input value.
     */
    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $default;
    }

    /**
     * Retrieve a boolean input value.
     */
    public function boolean(string $key, bool $default = false): bool
    {
        return $this->coerceBoolean($this->input($key, null), $default);
    }

    /**
     * Retrieve an input value as array.
     *
     * Accepts arrays and comma-separated strings.
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    public function arrayValue(string $key, array $default = []): array
    {
        return $this->normalizeArrayValue($this->input($key, null), $default);
    }

    /**
     * Return only the given input keys.
     *
     * @param array<int,string> $keys
     * @return array<string,mixed>
     */
    public function only(array $keys): array
    {
        $data = $this->all();
        $result = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $result[$key] = $data[$key];
            }
        }

        return $result;
    }

    /**
     * Return all input except the given keys.
     *
     * @param array<int,string> $keys
     * @return array<string,mixed>
     */
    public function except(array $keys): array
    {
        $data = $this->all();
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Get advanced filters from query string.
     *
     * Supported format:
     * - ?filter[name]=Jonas
     * - ?filter[age][gte]=18
     * - ?filter[id][in]=1,2,3
     *
     * @return array<string,mixed>
     */
    public function filters(): array
    {
        $filters = $this->query('filter', []);
        return is_array($filters) ? $filters : [];
    }

    /**
     * Parse sorting query parameter.
     *
     * Supported format:
     * - ?sort=name,-created_at
     * - ?sort=name:asc,created_at:desc
     *
     * @return array<int,array{column:string,direction:string}>
     */
    public function sort(): array
    {
        $sort = $this->query('sort', '');
        if ($sort === '' || $sort === null) {
            return [];
        }

        $parts = is_array($sort) ? $sort : explode(',', (string)$sort);
        $parsed = [];

        foreach ($parts as $item) {
            $token = trim((string)$item);
            if ($token === '') {
                continue;
            }

            $direction = 'ASC';
            $column = $token;

            if (str_starts_with($token, '-')) {
                $direction = 'DESC';
                $column = substr($token, 1);
            } elseif (str_starts_with($token, '+')) {
                $column = substr($token, 1);
            } elseif (str_contains($token, ':')) {
                [$column, $rawDirection] = explode(':', $token, 2);
                $direction = strtolower(trim($rawDirection)) === 'desc' ? 'DESC' : 'ASC';
            }

            $column = trim($column);
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
                continue;
            }

            $parsed[] = ['column' => $column, 'direction' => $direction];
        }

        return $parsed;
    }

    /**
     * Get current page from query.
     */
    public function page(int $default = 1): int
    {
        return max(1, $this->integer('page', $default));
    }

    /**
     * Get per-page size from query.
     */
    public function perPage(int $default = 15, int $max = 100): int
    {
        $perPage = max(1, $this->integer('per_page', $default));
        return min($perPage, max(1, $max));
    }

    /**
     * Get pagination params from query.
     *
     * @return array{page:int,per_page:int}
     */
    public function pagination(int $defaultPerPage = 15, int $maxPerPage = 100): array
    {
        return [
            'page' => $this->page(),
            'per_page' => $this->perPage($defaultPerPage, $maxPerPage),
        ];
    }

    /**
     * Apply filter/sort query params to a model query builder.
     *
     * @param Model $query
     * @param array<int,string> $allowedFilters
     * @param array<int,string> $allowedSorts
     * @return Model
     */
    public function applyFilters(Model $query, array $allowedFilters = [], array $allowedSorts = []): Model
    {
        foreach ($this->filters() as $column => $definition) {
            $column = (string)$column;
            if (!$this->isAllowedColumn($column, $allowedFilters)) {
                continue;
            }

            if (is_array($definition)) {
                if ($this->isAssoc($definition)) {
                    foreach ($definition as $operator => $value) {
                        $this->applyFilterOperation($query, $column, (string)$operator, $value);
                    }
                } else {
                    $this->applyFilterOperation($query, $column, 'in', $definition);
                }
                continue;
            }

            $this->applyFilterOperation($query, $column, 'eq', $definition);
        }

        $allowedSortColumns = $allowedSorts ?: $allowedFilters;
        foreach ($this->sort() as $sort) {
            if (!$this->isAllowedColumn($sort['column'], $allowedSortColumns)) {
                continue;
            }

            $query->orderBy($sort['column'], $sort['direction']);
        }

        return $query;
    }

    /**
     * Apply a single filter operation.
     *
     * @param Model $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    private function applyFilterOperation(Model $query, string $column, string $operator, $value): void
    {
        $operator = strtolower(trim($operator));

        switch ($operator) {
            case '=':
            case 'eq':
                $query->where($column, '=', $value);
                break;

            case '!=':
            case '<>':
            case 'ne':
                $query->where($column, '<>', $value);
                break;

            case '>':
            case 'gt':
                $query->where($column, '>', $value);
                break;

            case '>=':
            case 'gte':
                $query->where($column, '>=', $value);
                break;

            case '<':
            case 'lt':
                $query->where($column, '<', $value);
                break;

            case '<=':
            case 'lte':
                $query->where($column, '<=', $value);
                break;

            case 'like':
                $query->like($column, (string)$value);
                break;

            case 'starts_with':
            case 'starts':
                $query->startsWith($column, (string)$value);
                break;

            case 'ends_with':
            case 'ends':
                $query->endsWith($column, (string)$value);
                break;

            case 'in':
                $values = $this->normalizeArrayValue($value, []);
                if ($values !== []) {
                    $query->whereIn($column, $values);
                }
                break;

            case 'not_in':
                $values = $this->normalizeArrayValue($value, []);
                if ($values !== []) {
                    $query->whereNotIn($column, $values);
                }
                break;

            case 'between':
                $range = $this->normalizeArrayValue($value, []);
                if (count($range) >= 2) {
                    $query->whereBetween($column, $range[0], $range[1]);
                }
                break;

            case 'null':
            case 'is_null':
                if ($value === null || $this->coerceBoolean($value, true)) {
                    $query->whereNull($column);
                }
                break;

            case 'not_null':
            case 'is_not_null':
                if ($value === null || $this->coerceBoolean($value, true)) {
                    $query->whereNotNull($column);
                }
                break;

            default:
                $query->where($column, '=', $value);
                break;
        }
    }

    /**
     * Validate a column name and check allowlist, when provided.
     */
    private function isAllowedColumn(string $column, array $allowedColumns): bool
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
            return false;
        }

        return $allowedColumns === [] || in_array($column, $allowedColumns, true);
    }

    /**
     * Detect if an array is associative.
     */
    private function isAssoc(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Normalize filter values into arrays.
     *
     * @param mixed $value
     * @param array $default
     * @return array
     */
    private function normalizeArrayValue($value, array $default): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return $default;
            }

            if (str_contains($value, ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
            }

            return [$value];
        }

        if ($value === null) {
            return $default;
        }

        return [$value];
    }

    /**
     * Convert common truthy/falsy values to bool.
     */
    private function coerceBoolean($value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
                return false;
            }
        }

        return $default;
    }

    /**
     * Read input by key, including dot notation.
     *
     * @param array<string,mixed> $data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function dataGet(array $data, string $key, $default = null)
    {
        if ($key === '') {
            return $default;
        }

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $segments = explode('.', $key);
        $cursor = $data;

        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }

            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    /**
     * Decode request body (JSON first, then fallback to $_POST).
     *
     * @return array<string,mixed>
     */
    private function decodedBody(): array
    {
        if ($this->decodedBody !== null) {
            return $this->decodedBody;
        }

        $raw = $this->rawBody();
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->decodedBody = $decoded;
                return $this->decodedBody;
            }
        }

        $this->decodedBody = is_array($_POST ?? null) ? $_POST : [];
        return $this->decodedBody;
    }

    /**
     * Read and cache raw body.
     */
    private function rawBody(): string
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input') ?: '';
        }

        return $this->rawBody;
    }
}
