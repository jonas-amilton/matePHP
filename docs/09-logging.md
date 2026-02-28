# 09 - Logging

## Visao geral

O projeto possui:

- `Framework\\Log\\Logger`
- helper global `logger()` em `framework/helpers/log.php`
- configuracao em `framework/Log/logging.php`

## Canais disponiveis por padrao

- `app` (arquivo `storage/logs/app.log`)
- `errors` (arquivo `storage/logs/errors.log`)
- `console` (stdout)

## Uso com helper

```php
logger('info', 'Iniciando processo');
logger('error', 'Falha ao salvar usuario', 'errors');
logger('debug', 'Payload recebido', 'console');
```

## Uso direto do Logger

```php
use Framework\Log\Logger;

$log = Logger::getInstance();
$log->info('Mensagem padrao');
$log->channel('errors')->error('Erro de validacao');
```

## Formato de saida

Entradas sao gravadas como:

```text
[YYYY-MM-DD HH:ii:ss] LEVEL: mensagem
```

## Ajustando configuracao

Edite `framework/Log/logging.php` para:

- trocar canal default
- adicionar novos canais
- alterar paths
- trocar driver (`single` ou `stdout`)
