# 🧱 matePHP — Framework PHP para APIs REST

Framework minimalista inspirado no Laravel, mas construído em **PHP puro**, ideal para estudar as bases de **migrations**, **seeders**, **jobs**, **workers**, **controllers**, **routes** e **models**.

---

## 🚀 Opção 1: Rodar com Docker (recomendado)

### 1. Subir containers

```bash
docker compose up -d
```

> Caso veja erro de permissão, rode com `sudo docker compose up -d`
> ou adicione seu usuário ao grupo docker:
>
> ```bash
> sudo usermod -aG docker $USER && newgrp docker
> ```

### 2. Acessar phpMyAdmin

Acesse: [http://localhost:8989](http://localhost:8989)

Credenciais padrão:

```
Servidor: db
Usuário: root
Senha: root
```

Crie o banco de dados `matephp`.

---

### 3. Configurar o ambiente

Crie e edite o arquivo `.env`:

```bash
cp .env.example .env
```

Configure:

```
DB_DRIVER=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=matephp
DB_USERNAME=root
DB_PASSWORD=root
```

---

### 4. Rodar migrations

Dentro do container PHP:

```bash
docker exec -it matephp-php bash
php cli.php migrate
php cli.php seed
```

---

### 5. Testar a API

Acesse no navegador ou via curl:

```
GET  http://localhost:8001/api/hello
```

Deve retornar:

```json
{ "message": "Hello, world!" }
```

---

## 🧩 Opção 2: Rodar localmente (sem Docker)

1. Inicie o MySQL local (XAMPP, Laragon, etc.)
2. Crie o banco `matephp`
3. Edite `.env` com suas credenciais locais:
   ```bash
   cp .env.example .env
   ```
4. Gere o autoload e rode as migrations:
   ```bash
   composer dump-autoload
   php cli.php migrate
   ```
5. Suba o servidor embutido do PHP:
   ```bash
   php -S localhost:8000 -t public
   ```
6. Teste:
   ```
   GET  http://localhost:8000/api/hello
   ```

---

## ⚙️ Opção 3: Rodar com Apache e VirtualHost (ambiente local)

1. Crie um VirtualHost no Apache apontando para `public/`:

   ```apache
   <VirtualHost *:80>
       ServerName matephp.local
       DocumentRoot "C:/caminho/para/matePHP/public"

       <Directory "C:/caminho/para/matePHP/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. Adicione no arquivo `hosts`:

   ```
   127.0.0.1 matephp.local
   ```

3. Reinicie o Apache e acesse:
   [http://matephp.local/api/hello](http://matephp.local/api/hello)

---

## 🧠 Dicas úteis

- Para reiniciar o ambiente Docker limpo:

  ```bash
  docker compose down -v --remove-orphans
  docker compose up -d --build
  ```

- Para entrar no container PHP:
  ```bash
  docker exec -it matephp-php bash
  ```

---

## 🛠️ Comandos de geração

```bash
php cli.php make:controller UserController
php cli.php make:model User
php cli.php make:migration create_users_table
php cli.php make:seeder UserSeeder
php cli.php make:middleware AuthMiddleware
```

---

## 🔎 Filtros avançados de requisição

O `Request` agora suporta filtros, ordenação e paginação via query string:

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

---

## 📁 Estrutura do projeto

```
matePHP/
│
├── app/                # Controllers, Middlewares e Models
├── framework/          # Core do framework
├── public/             # index.php principal
├── database/
│   ├── migrations/
│   └── seeders/
├── cli.php             # Console kernel (migrate, seed etc.)
├── docker/
│   ├── php/
│   │   └── Dockerfile
│   └── nginx.conf
├── docker-compose.yml
├── composer.json
└── .env.example
```

---

Desenvolvido para fins educacionais.  
💡 **matePHP** é um framework didático para entender como o Laravel funciona por dentro.
