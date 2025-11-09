<?php


use Framework\Core\Router;
use App\Http\Controllers\ExampleController;


$router = new Router();


$router->get('/api/hello', [ExampleController::class, 'hello']);
$router->get('/api/users', [ExampleController::class, 'index']);


return $router;
