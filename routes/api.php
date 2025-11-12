<?php

use Framework\Core\Route;
use App\Http\Controllers\ExampleController;

/**
 * ------------------------------------------------------------
 *  API Routes
 * ------------------------------------------------------------
 * Here you can define all API endpoints for your application.
 * 
 * Examples:
 *  - Route::get('/users', [UserController::class, 'index']);
 *  - Route::post('/users', [UserController::class, 'store']);
 *  - Route::resource('posts', PostController::class);
 *  - Route::group(['prefix' => 'admin'], function ($r) {
 *        $r->get('/dashboard', [AdminController::class, 'index']);
 *    });
 */

Route::get('/api/hello', [ExampleController::class, 'hello']);
Route::get('/api/users', [ExampleController::class, 'index']);