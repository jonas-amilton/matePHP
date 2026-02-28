# 08 - Testes

## Stack atual

- PHPUnit 12
- Configuracao principal em `phpunit.xml` na raiz
- Estrutura de testes no padrao Laravel:
  - `tests/TestCase.php`
  - `tests/Feature`
  - `tests/Unit`

## Comandos

Rodar tudo:

```bash
composer test
# ou
composer phpunit
```

Rodar apenas um arquivo:

```bash
./vendor/bin/phpunit tests/Feature/ModelTest.php
```

Rodar um metodo especifico:

```bash
./vendor/bin/phpunit --filter test_paginate_respects_current_query_filters tests/Feature/ModelTest.php
```

## Cobertura atual relevante

`tests/Feature/ModelTest.php` cobre, entre outros pontos:

- query builder
- CRUD
- soft delete
- relacionamentos
- casts/accessors/mutators
- macros/global scopes
- eventos/observers
- transacoes
- agregacoes
- paginacao

## Dicas

- Sempre rode a suite antes de subir alteracoes no core (`framework/Core`)
- Ao adicionar recurso novo no `Model`, inclua teste cobrindo caminho feliz e edge cases
