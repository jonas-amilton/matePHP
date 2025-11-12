<?php

namespace Framework\Core;

use Closure;

/**
 * Class Route
 *
 * Static facade for the Router class, providing a Laravel-like routing API.
 * Allows defining routes using static methods such as Route::get(), Route::post(), etc.
 *
 * Example usage:
 * ```php
 * Route::get('/users', [UserController::class, 'index']);
 * Route::resource('posts', PostController::class);
 * Route::group(['prefix' => 'admin'], function ($r) {
 *     $r->get('/dashboard', [DashboardController::class, 'index']);
 * });
 * ```
 *
 * @package Framework\Core
 */
class Route
{
    /**
     * Holds the Router instance used by the facade.
     *
     * @var Router|null
     */
    private static ?Router $router = null;

    /**
     * Set the Router instance for the Route facade.
     *
     * This allows dependency injection of the Router so that the facade
     * uses the same instance across the application.
     *
     * @param Router $router
     * @return void
     */
    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }

    /**
     * Retrieve the current Router instance or create one if none is set.
     *
     * @return Router
     */
    private static function router(): Router
    {
        if (!self::$router) {
            self::$router = new Router();
        }
        return self::$router;
    }

    /**
     * Register a GET route.
     *
     * @param string         $uri
     * @param callable|array $action
     * @return void
     */
    public static function get(string $uri, array|callable $action): void
    {
        self::router()->get($uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param string         $uri
     * @param callable|array $action
     * @return void
     */
    public static function post(string $uri, array|callable $action): void
    {
        self::router()->post($uri, $action);
    }

    /**
     * Register a PUT route.
     *
     * @param string         $uri
     * @param callable|array $action
     * @return void
     */
    public static function put(string $uri, array|callable $action): void
    {
        self::router()->put($uri, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param string         $uri
     * @param callable|array $action
     * @return void
     */
    public static function patch(string $uri, array|callable $action): void
    {
        self::router()->patch($uri, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param string         $uri
     * @param callable|array $action
     * @return void
     */
    public static function delete(string $uri, array|callable $action): void
    {
        self::router()->delete($uri, $action);
    }

    /**
     * Register a RESTful resource controller route set.
     *
     * Automatically creates the standard CRUD routes:
     * index, create, store, show, edit, update, destroy.
     *
     * @param string $name        Resource name (e.g. "users")
     * @param string $controller  Controller class name
     * @return void
     */
    public static function resource(string $name, string $controller): void
    {
        self::router()->resource($name, $controller);
    }

    /**
     * Define a group of routes that share attributes such as prefix or middleware.
     *
     * Example:
     * ```php
     * Route::group(['prefix' => 'admin', 'middleware' => [Auth::class]], function ($r) {
     *     $r->get('/dashboard', [DashboardController::class, 'index']);
     * });
     * ```
     *
     * @param array   $attributes  Group attributes
     * @param Closure $callback    Callback that defines the grouped routes
     * @return void
     */
    public static function group(array $attributes, Closure $callback): void
    {
        self::router()->group($attributes, $callback);
    }

    /**
     * Assign a name to the most recently registered route.
     *
     * Allows for route name referencing when generating URLs later.
     *
     * @param string $name
     * @return void
     */
    public static function name(string $name): void
    {
        self::router()->name($name);
    }

    /**
     * Dispatch the given HTTP request through the router.
     *
     * @param Request $request
     * @return mixed
     */
    public static function dispatch(Request $request): mixed
    {
        return self::router()->dispatch($request);
    }
}