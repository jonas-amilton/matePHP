# 02 - Arquitetura e Fluxo da Aplicacao

## Fluxo HTTP

1. Entrada em `public/index.php`
2. Carregamento do autoload do Composer
3. Inicializacao do `Router` e da facade `Route`
4. Inclusao das rotas em `routes/api.php`
5. Captura do request via `Request::capture()`
6. Dispatch para rota/controller no `Router`
7. Retorno da resposta

## Fluxo CLI

1. Entrada em `cli.php`
2. Leitura de `.env` (se existir)
3. Encaminhamento para `Framework\Console\Kernel::handle($argv)`
4. Execucao do comando selecionado

## Modulos principais

- `framework/Core`
  - `Request`: leitura e manipulacao de entrada HTTP
  - `Response`: resposta JSON
  - `Router` e `Route`: definicao e dispatch de rotas
  - `Model`: ORM com query builder e recursos inspirados no Eloquent
  - `Database`: conexao PDO
- `framework/Console`
  - Kernel e comandos `migrate`, `seed`, `make:*`
- `app`
  - Controllers, Models e Middlewares da aplicacao
- `database`
  - Migrations e seeders
- `tests`
  - Suites de `Feature` e `Unit`

## Convencoes atuais

- Rotas da API ficam em `routes/api.php`
- Controllers em `app/Http/Controllers`
- Models em `app/Models`
- Middlewares em `app/Http/Middlewares`
- Testes seguem estrutura estilo Laravel (`tests/Feature` e `tests/Unit`)
