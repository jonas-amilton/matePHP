<?php

use Framework\Core\Router;
use Framework\Core\Route;
use Framework\Core\Request;

/**
 * ------------------------------------------------------------
 *  Application Bootstrap File
 * ------------------------------------------------------------
 * This file is the main entry point for all HTTP requests.
 * It handles:
 *  - Autoloading dependencies
 *  - Initializing the router and Route facade
 *  - Loading route definitions
 *  - Dispatching the current request
 *  - Sending the response back to the client
 *  - Loading environment variables from the .env file
 */

// ------------------------------------------------------------
// Load Composer Autoloader
// ------------------------------------------------------------
require_once __DIR__ . '/../vendor/autoload.php';

// ------------------------------------------------------------
// Initialize Router and Facade
// ------------------------------------------------------------
$router = new Router();
Route::setRouter($router);

// ------------------------------------------------------------
// Load API Routes
// ------------------------------------------------------------
/**
 * Include your route definitions from the routes/api.php file.
 * This file should register routes using the Route facade, e.g.:
 * 
 * ```php
 * Route::get('/users', [UserController::class, 'index']);
 * Route::post('/users', [UserController::class, 'store']);
 * ```
 *
 * @var Router $router
 */
$router = require __DIR__ . '/../routes/api.php';

// ------------------------------------------------------------
// Capture Request and Dispatch Route
// ------------------------------------------------------------
/**
 * Create a new Request instance from the current HTTP context,
 * then dispatch it through the Router to find and execute the
 * corresponding controller or closure action.
 *
 * @var Request $request
 * @var mixed $response
 */
$request = Request::capture();
$response = Route::dispatch($request);

// ------------------------------------------------------------
// Send Response to Client
// ------------------------------------------------------------
/**
 * If the response is an object with a `send()` method (such as
 * a Response class), invoke it. Otherwise, echo the response
 * directly (for strings, arrays, or raw JSON).
 */
if (method_exists($response, 'send')) {
    $response->send();
} else {
    echo $response;
}

// ------------------------------------------------------------
// Load Environment Variables (.env)
// ------------------------------------------------------------
/**
 * If a .env file exists in the project root, parse it and set
 * its values into the global $_ENV array for later use.
 *
 * This allows sensitive configuration (database, API keys, etc.)
 * to be kept outside of version control.
 */
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $k => $v) {
        $_ENV[$k] = $v;
    }
}