# matePHP

Framework PHP minimalista para APIs REST, com foco educacional para entender como funciona um stack inspirado em Laravel (roteamento, request, model, migrations, seeders e CLI).

## Requisitos

- PHP 8.0+
- Composer
- MySQL 8+ (ou Docker)

## Como rodar

### Opção 1: Docker

1. Suba os containers:

```bash
docker compose up -d
```

2. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

3. Configure o `.env` para o ambiente Docker:

```env
DB_DRIVER=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=matephp
DB_USERNAME=root
DB_PASSWORD=root
```

4. Rode migrations e seeders:

```bash
docker exec -it matephp-php bash
php cli.php migrate
php cli.php seed
```

5. Acesse a API:

- `http://localhost:8001/api/hello`
- `http://localhost:8001/api/users`

Serviços auxiliares:

- phpMyAdmin: `http://localhost:8989`
- MySQL (host): `127.0.0.1:3307`

### Opção 2: Local (sem Docker)

1. Copie o ambiente:

```bash
cp .env.example .env
```

2. Ajuste seu `.env` (exemplo):

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=matephp
DB_USERNAME=jonas
DB_PASSWORD=root
```

3. Instale dependências e autoload:

```bash
composer install
composer dump-autoload
```

4. Rode migrations e seeders:

```bash
php cli.php migrate
php cli.php seed
```

5. Suba servidor local:

```bash
php -S localhost:8000 -t public
```

6. Teste:

- `http://localhost:8000/api/hello`
- `http://localhost:8000/api/users`

## Rotas atuais

Definidas em `routes/api.php`:

- `GET /api/hello` retorna mensagem de teste
- `GET /api/users` retorna usuários e aceita filtros/paginação

## Filtros avançados de requisição

O `Request` suporta filtros, ordenação e paginação via query string.

Exemplo:

```http
GET /api/users?filter[name][like]=jo&filter[id][in]=1,2,3&sort=-created_at&page=1&per_page=10
```

Formatos aceitos:

- `filter[campo]=valor` (equivalente a `eq`)
- `filter[campo][eq|ne|gt|gte|lt|lte]=valor`
- `filter[campo][like|starts_with|ends_with]=valor`
- `filter[campo][in|not_in]=1,2,3`
- `filter[campo][between]=10,20`
- `filter[campo][null|not_null]=1`
- `sort=campo,-outro_campo` ou `sort=campo:asc,outro:desc`
- `page` e `per_page` para paginação

No endpoint de exemplo (`/api/users`), os campos permitidos para filtro e ordenação são:

- `id`
- `name`
- `email`
- `created_at`
- `updated_at`

## CLI

Comandos disponíveis:

```bash
php cli.php migrate
php cli.php seed
php cli.php make:controller NomeController
php cli.php make:controller NomeController --resource
php cli.php make:model NomeModel
php cli.php make:migration nome_da_migration
php cli.php make:seeder NomeSeeder
php cli.php make:middleware NomeMiddleware
```

## Testes

A suíte usa PHPUnit (configuração em `phpunit.xml` na raiz).

Comandos:

```bash
composer test
# ou
composer phpunit
```

Estrutura de testes no padrão Laravel:

- `tests/Feature`
- `tests/Unit`
- `tests/TestCase.php`

## Estrutura do projeto

```text
matePHP/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middlewares/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── framework/
│   ├── Console/
│   ├── Core/
│   └── helpers/
├── public/
├── routes/
├── tests/
├── cli.php
├── composer.json
├── docker-compose.yml
└── phpunit.xml
```

## Observações

- O projeto é didático e privilegia legibilidade sobre abstrações avançadas.
- A pasta `routes` usa `api.php` como ponto principal de rotas no estado atual.
