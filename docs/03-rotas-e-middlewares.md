# 03 - Rotas e Middlewares

## Definindo rotas

A API usa a facade `Framework\Core\Route`.

```php
use Framework\Core\Route;
use App\Http\Controllers\ExampleController;

Route::get('/api/hello', [ExampleController::class, 'hello']);
Route::post('/api/users', [ExampleController::class, 'store']);
Route::put('/api/users/{id}', [ExampleController::class, 'update']);
Route::delete('/api/users/{id}', [ExampleController::class, 'destroy']);
```

## Parametros dinamicos

Placeholders no formato `{id}` viram parametros no metodo do controller.

```php
Route::get('/api/users/{id}', [UserController::class, 'show']);
```

Metodo no controller:

```php
public function show(Request $request, $id)
{
    // ...
}
```

## Grupos de rota

Voce pode agrupar por `prefix` e/ou `middleware`:

```php
Route::group(['prefix' => 'api/admin'], function ($r) {
    $r->get('/dashboard', [AdminController::class, 'index']);
});
```

## Middleware em rotas

No estado atual, middleware e aplicado via `Route::group`.

```php
use App\Http\Middlewares\AuthMiddleware;

Route::group(['middleware' => [AuthMiddleware::class]], function ($r) {
    $r->get('/api/users', [UserController::class, 'index']);
});
```

### Estrutura de um middleware

```php
namespace App\Http\Middlewares;

use Framework\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        // regra de autenticacao/autorizacao
    }
}
```

## Rotas resource

Existe suporte para `Route::resource('users', UserController::class)`, gerando:

- `GET /users`
- `GET /users/create`
- `POST /users`
- `GET /users/{id}`
- `GET /users/{id}/edit`
- `PUT /users/{id}`
- `DELETE /users/{id}`
