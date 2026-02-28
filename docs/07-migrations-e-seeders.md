# 07 - Migrations e Seeders

## Estrutura de migration

Cada migration retorna um objeto anonimo com metodos `up` e `down`.

Exemplo minimo:

```php
<?php

return new class {
    public function up(PDO $pdo)
    {
        // criar/alterar estruturas
    }

    public function down(PDO $pdo)
    {
        // rollback
    }
};
```

## Como rodar migrations

```bash
php cli.php migrate
```

Comportamento atual:

- Le todos os arquivos de `database/migrations/*.php`
- Executa `up($pdo)` para cada um
- Nao ha tabela de controle de versao de migration

Isso significa que a idempotencia deve ser tratada dentro da propria migration (por exemplo: `CREATE TABLE IF NOT EXISTS`).

## Seeders

- Arquivo principal: `database/seeders/DatabaseSeeder.php`
- Exemplo de seeder: `database/seeders/UserSeeder.php`

Executar:

```bash
php cli.php seed
```

## Geracao automatica

```bash
php cli.php make:migration nome_da_migration
php cli.php make:seeder NomeSeeder
```

## Boas praticas recomendadas

- Evite SQL destrutivo sem rollback equivalente
- Mantenha migrations pequenas e focadas
- Use seeders para dados de desenvolvimento e carga inicial
- Em seeders, considere checagens para evitar duplicacao quando necessario
