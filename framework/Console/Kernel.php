<?php

namespace Framework\Console;

use Database\Seeders\UserSeeder;
use Framework\Core\Database;

class Kernel
{
    public static function handle(array $argv)
    {
        $cmd = $argv[1] ?? null;
        switch ($cmd) {
            case 'migrate':
                self::migrate();
                break;
            case 'seed':
                self::seed();
                break;
            default:
                echo "Available commands: migrate, seed\n";
        }
    }

    private static function migrate()
    {
        $files = glob(__DIR__ . '/../../database/migrations/*.php');
        $pdo = Database::getConnection();
        foreach ($files as $f) {
            $migration = require $f;
            $migration->up($pdo);
            echo "Migrated: $f\n";
        }
    }

    private static function seed()
    {
        $pdo = Database::getConnection();
        (new UserSeeder())->run($pdo);
    }
}
