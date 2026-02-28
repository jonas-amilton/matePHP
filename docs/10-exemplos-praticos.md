# 10 - Exemplos Praticos da API

## Exemplo 1: rota basica

Arquivo `routes/api.php`:

```php
use Framework\Core\Route;
use App\Http\Controllers\ExampleController;

Route::get('/api/hello', [ExampleController::class, 'hello']);
```

Controller:

```php
public function hello(Request $request)
{
    return Response::json(['message' => 'Ola mundo']);
}
```

## Exemplo 2: listagem com filtros e pagina

Rota:

```php
Route::get('/api/users', [ExampleController::class, 'index']);
```

Controller:

```php
public function index(Request $request)
{
    $query = new User();

    $request->applyFilters(
        $query,
        ['id', 'name', 'email', 'created_at', 'updated_at'],
        ['id', 'name', 'email', 'created_at', 'updated_at']
    );

    if ($request->has('page') || $request->has('per_page')) {
        $pagination = $request->pagination(15, 100);
        return Response::json($query->paginate($pagination['per_page'], $pagination['page']));
    }

    return Response::json($query->get());
}
```

Chamadas:

```http
GET /api/users?filter[name][like]=jo
GET /api/users?sort=-created_at
GET /api/users?page=2&per_page=10
GET /api/users?filter[id][in]=1,2,3&sort=name:asc
```

## Exemplo 3: middleware em grupo

```php
use App\Http\Middlewares\AuthMiddleware;

Route::group(['middleware' => [AuthMiddleware::class]], function ($r) {
    $r->get('/api/users', [UserController::class, 'index']);
});
```

## Exemplo 4: criacao de artefatos via CLI

```bash
php cli.php make:controller UserController --resource
php cli.php make:model User
php cli.php make:migration create_users_table
php cli.php make:seeder UserSeeder
php cli.php make:middleware AuthMiddleware
```

## Exemplo 5: model com recursos do ORM

```php
$active = (new User())
    ->where('active', '=', 1)
    ->orderBy('id', 'DESC')
    ->limit(20)
    ->get();

$user = User::firstOrCreate(
    ['email' => 'jonas@example.com'],
    ['name' => 'Jonas']
);

$page = (new User())->paginate(10, 1);
```
