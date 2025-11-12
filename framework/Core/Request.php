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
     * Retrieve input data from the request body or query.
     *
     * Automatically decodes JSON payloads or falls back to `$_POST`.
     *
     * Examples:
     * ```php
     * $name = $request->input('name');
     * $all  = $request->input(); // all input data
     * ```
     *
     * @param string|null $key The input key to retrieve, or null to get all data
     * @return mixed The value of the input key, or an array of all input data
     */
    public function input(?string $key = null)
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    /**
     * Get all input data from the current request.
     *
     * Attempts to decode JSON payloads, falling back to `$_POST`
     * when the body is not valid JSON.
     *
     * @return array<string, mixed> The full input data as an associative array
     */
    public function all(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        return $data;
    }
}