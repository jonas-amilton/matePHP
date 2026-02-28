# 05 - Model e ORM

## Criando um model

```php
namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected array $fillable = ['name', 'email'];
}
```

## Consulta (query builder)

Metodos principais:

- `where`, `orWhere`
- `like`, `startsWith`, `endsWith`
- `whereIn`, `whereNotIn`, `whereBetween`
- `whereNull`, `whereNotNull`
- `join`
- `orderBy`, `limit`, `offset`
- `get`, `first`, `toSql`

Exemplo:

```php
$users = (new User())
    ->where('email', 'LIKE', '%@empresa.com')
    ->orderBy('id', 'DESC')
    ->limit(10)
    ->get();
```

## CRUD

- `User::all()`
- `User::find($id)`
- `User::create($data)`
- `User::update($id, $data)`
- `User::delete($id)`
- `User::forceDelete($id)`

## Upsert helpers

- `firstOrCreate($attributes, $values = [])`
- `updateOrCreate($attributes, $values)`
- `createOrUpdate($data, $uniqueColumn)`

Observacao: `createOrUpdate` exige que `$uniqueColumn` exista em `$data`.

## Paginacao

```php
$page = (new User())
    ->where('active', '=', 1)
    ->paginate(15, 2);
```

Retorno:

- `data`
- `total`
- `per_page`
- `current_page`
- `last_page`

## Agregacoes

- `count()`
- `sum('coluna')`
- `avg('coluna')`
- `min('coluna')`
- `max('coluna')`

## Soft delete

Para habilitar em um model:

```php
protected static bool $softDelete = true;
```

Metodos relacionados:

- `withTrashed()`
- `onlyTrashed()`
- `restore($id)`

## Relacionamentos

Disponiveis:

- `hasOne`
- `hasMany`
- `belongsTo`
- `belongsToMany`
- `morphTo`
- `morphMany`

Exemplo:

```php
public function profile(): ?array
{
    return $this->hasOne(Profile::class, 'user_id', 'id');
}
```

## Casts, accessors e mutators

- Defina `protected array $casts`
- Accessor: `getNomeAttribute`
- Mutator: `setNomeAttribute`

Exemplo de cast:

```php
protected array $casts = [
    'age' => 'int',
    'active' => 'bool',
    'meta' => 'json',
    'created_at' => 'datetime',
];
```

## Macros e global scopes

- `Model::macro('nome', fn() => ...)`
- `Model::addGlobalScope('nome', fn($query) => ...)`

## Eventos e observers

Eventos suportados:

- `creating`, `created`
- `updating`, `updated`
- `deleting`, `deleted`
- `restoring`, `restored`

Registro:

```php
User::on('creating', function ($payload) {
    // ...
});

User::observe(UserObserver::class);
```

## Transacoes

- `Model::beginTransaction()`
- `Model::commit()`
- `Model::rollback()`

## Concurrency lock

- `lockForUpdate($column, $operator, $value)`

Exemplo:

```php
$user = (new User())->lockForUpdate('id', '=', 1);
```
