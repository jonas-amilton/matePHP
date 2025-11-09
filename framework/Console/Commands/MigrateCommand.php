<?php

namespace Framework\Console\Commands;

use Framework\Core\Database;

class MigrateCommand implements \Framework\Console\Command
{
    public function handle(array $args): void
    {
        $files = glob(__DIR__ . '/../../../database/migrations/*.php');
        $pdo = Database::getConnection();

        foreach ($files as $f) {
            $migration = require $f;
            $migration->up($pdo);
            echo "Migrated: $f\n";
        }
    }
}
