# 01 - Instalacao e Execucao

## Requisitos

- PHP 8.0+
- Composer
- MySQL 8+
- Docker (opcional)

## Instalacao local

1. Instale dependencias:

```bash
composer install
```

2. Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

3. Configure o banco no `.env`:

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=matephp
DB_USERNAME=jonas
DB_PASSWORD=root
```

4. Rode migrations e seeders:

```bash
php cli.php migrate
php cli.php seed
```

5. Suba o servidor:

```bash
php -S localhost:8000 -t public
```

## Instalacao com Docker

1. Suba os servicos:

```bash
docker compose up -d
```

2. Crie o `.env` e ajuste para o container de banco:

```env
DB_DRIVER=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=matephp
DB_USERNAME=root
DB_PASSWORD=root
```

3. Execute migrations e seeders no container PHP:

```bash
docker exec -it matephp-php bash
php cli.php migrate
php cli.php seed
```

4. Acesse:

- API: `http://localhost:8001`
- phpMyAdmin: `http://localhost:8989`
- MySQL (host): `127.0.0.1:3307`

## Rotas de verificacao rapida

- `GET /api/hello`
- `GET /api/users`
