# 06 - CLI e Geradores

## Entrada da CLI

Comandos sao executados via:

```bash
php cli.php <comando>
```

## Comandos disponiveis

- `migrate`
- `seed`
- `make:controller`
- `make:model`
- `make:seeder`
- `make:migration`
- `make:middleware`

## Geradores

### make:controller

```bash
php cli.php make:controller UserController
php cli.php make:controller Admin/UserController
php cli.php make:controller UserController --resource
```

Destino: `app/Http/Controllers`

Observacao: no modo `--resource`, o comando tenta anexar rota resource em `routes/web.php` apenas se o arquivo existir e for gravavel.

### make:model

```bash
php cli.php make:model User
php cli.php make:model Admin/User
```

Destino: `app/Models`

### make:middleware

```bash
php cli.php make:middleware AuthMiddleware
php cli.php make:middleware Admin/AuthMiddleware
```

Destino: `app/Http/Middlewares`

### make:migration

```bash
php cli.php make:migration create_users_table
```

Destino: `database/migrations` com timestamp no nome.

### make:seeder

```bash
php cli.php make:seeder UserSeeder
```

Destino: `database/seeders`

## Execucao de banco

### migrate

```bash
php cli.php migrate
```

Executa todos os arquivos em `database/migrations/*.php` chamando `up($pdo)`.

### seed

```bash
php cli.php seed
```

Executa `Database\\Seeders\\DatabaseSeeder`.
