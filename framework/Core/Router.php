<?php

namespace Framework\Core;

use Closure;

/**
 * Class Router
 *
 * Handles HTTP route registration, grouping, middleware, and dispatching.
 * Provides basic RESTful resource routing and Laravel-like syntax.
 *
 * @package Framework\Core
 */
class Router
{
    /**
     * Registered routes, organized by HTTP method.
     *
     * @var array<string, array<string, array{action: callable|array, name: string|null, middleware: array}>>
     */
    private array $routes = [];

    /**
     * Stack of current route groups and their attributes.
     *
     * @var array<int, array{prefix?: string, middleware?: array|string}>
     */
    private array $groupStack = [];

    /**
     * Register a GET route.
     *
     * @param string              $uri
     * @param callable|array      $action
     * @return void
     */
    public function get(string $uri, array|callable $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param string              $uri
     * @param callable|array      $action
     * @return void
     */
    public function post(string $uri, array|callable $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a PUT route.
     *
     * @param string              $uri
     * @param callable|array      $action
     * @return void
     */
    public function put(string $uri, array|callable $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param string              $uri
     * @param callable|array      $action
     * @return void
     */
    public function patch(string $uri, array|callable $action): void
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param string              $uri
     * @param callable|array      $action
     * @return void
     */
    public function delete(string $uri, array|callable $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a route for a specific HTTP method.
     *
     * @param string              $method   HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string              $uri      Route URI pattern
     * @param callable|array      $action   Controller method or Closure
     * @param string|null         $name     Optional route name
     * @return void
     */
    private function addRoute(string $method, string $uri, array|callable $action, ?string $name = null): void
    {
        $uri = $this->applyGroupPrefix($this->normalize($uri));
        $this->routes[$method][$uri] = [
            'action' => $action,
            'name' => $name,
            'middleware' => $this->currentGroupMiddleware(),
        ];
    }

    /**
     * Normalize a URI by ensuring a leading slash and removing trailing slashes.
     *
     * @param string $uri
     * @return string
     */
    private function normalize(string $uri): string
    {
        return rtrim('/' . ltrim(parse_url($uri, PHP_URL_PATH), '/'), '/') ?: '/';
    }

    /**
     * Apply prefix from the current route group stack to a given URI.
     *
     * @param string $uri
     * @return string
     */
    private function applyGroupPrefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (!empty($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        return $this->normalize($prefix . '/' . ltrim($uri, '/'));
    }

    /**
     * Retrieve all active middlewares from the current group stack.
     *
     * @return array
     */
    private function currentGroupMiddleware(): array
    {
        $middlewares = [];
        foreach ($this->groupStack as $group) {
            if (!empty($group['middleware'])) {
                $middlewares = array_merge($middlewares, (array) $group['middleware']);
            }
        }
        return $middlewares;
    }

    /**
     * Define a group of routes that share attributes such as prefix or middleware.
     *
     * @param array    $attributes  Attributes such as ['prefix' => 'admin', 'middleware' => [Auth::class]]
     * @param Closure  $callback    Group definition callback
     * @return void
     */
    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Assign a name to the most recently registered route.
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $lastMethod = array_key_last($this->routes);
        $lastUri = array_key_last($this->routes[$lastMethod]);
        $this->routes[$lastMethod][$lastUri]['name'] = $name;
        return $this;
    }

    /**
     * Register a set of RESTful routes for a controller.
     * Automatically generates index, create, store, show, edit, update, and destroy routes.
     *
     * @param string $name        Resource name (e.g. "users")
     * @param string $controller  Controller class name
     * @return void
     */
    public function resource(string $name, string $controller): void
    {
        $base = '/' . trim($name, '/');
        $this->get($base, [$controller, 'index']);
        $this->get("$base/create", [$controller, 'create']);
        $this->post($base, [$controller, 'store']);
        $this->get("$base/{id}", [$controller, 'show']);
        $this->get("$base/{id}/edit", [$controller, 'edit']);
        $this->put("$base/{id}", [$controller, 'update']);
        $this->delete("$base/{id}", [$controller, 'destroy']);
    }

    /**
     * Dispatch the incoming HTTP request to the appropriate route.
     * Handles dynamic parameters, middleware execution, and controller invocation.
     *
     * @param Request $request
     * @return mixed
     */
    public function dispatch(Request $request)
    {
        $method = $request->method();
        $uri = $this->normalize($request->uri());

        foreach ($this->routes[$method] ?? [] as $routeUri => $route) {
            // Convert {param} placeholders to regex
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $routeUri);

            if (preg_match("#^$pattern$#", $uri, $matches)) {
                array_shift($matches);

                [$controllerClass, $methodName] = is_array($route['action'])
                    ? $route['action']
                    : [null, null];

                // Execute middleware stack
                foreach ($route['middleware'] as $mw) {
                    $mwInstance = new $mw();
                    $mwInstance->handle($request);
                }

                // Call controller or closure
                if (is_array($route['action'])) {
                    $controller = new $controllerClass();
                    return $controller->$methodName($request, ...$matches);
                }

                return $route['action']($request, ...$matches);
            }
        }

        return Response::json(['error' => 'Not Found'], 404);
    }
}