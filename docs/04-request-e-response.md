# 04 - Request e Response

## Classe Request

`Framework\Core\Request` oferece utilitarios para query string, body e filtros.

### Captura

```php
$request = Request::capture();
```

### Metodos basicos

- `method()`
- `uri()`
- `query($key = null, $default = null)`
- `body($key = null, $default = null)`
- `input($key = null, $default = null)`
- `all()`

### Utilitarios

- `has('campo')`
- `filled('campo')`
- `integer('campo', 0)`
- `boolean('campo', false)`
- `arrayValue('campo', [])`
- `only(['a', 'b'])`
- `except(['senha'])`

## Filtros avancados

### Leitura de filtros/sort/paginacao

- `filters()`
- `sort()`
- `page()`
- `perPage()`
- `pagination()`

### Aplicacao automatica no Model

```php
$query = new User();

$request->applyFilters(
    $query,
    ['id', 'name', 'email', 'created_at', 'updated_at'],
    ['id', 'name', 'email', 'created_at', 'updated_at']
);
```

### Operadores aceitos em `filter`

- `eq`, `ne`
- `gt`, `gte`, `lt`, `lte`
- `like`, `starts_with`, `ends_with`
- `in`, `not_in`
- `between`
- `null`, `not_null`

Exemplo:

```http
GET /api/users?filter[name][like]=jo&filter[id][in]=1,2,3&sort=-created_at&page=1&per_page=10
```

## Classe Response

`Framework\Core\Response` possui retorno JSON:

```php
return Response::json(['ok' => true], 200);
```

No estado atual, `Response::json` envia headers/status e faz `echo` do payload JSON.
