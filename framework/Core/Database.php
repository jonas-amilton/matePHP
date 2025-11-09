<?php

namespace Framework\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {

            $envPath = __DIR__ . '/../../.env';

            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with($line, '#')) continue;
                    [$name, $value] = array_map('trim', explode('=', $line, 2));
                    $_ENV[$name] = $value;
                }
            }

            $driver   = $_ENV['DB_DRIVER'] ?? 'mysql';
            $host     = $_ENV['DB_HOST'] ?? 'db';
            $port     = $_ENV['DB_PORT'] ?? '3306';
            $dbname   = $_ENV['DB_DATABASE'] ?? 'matephp';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? 'root';

            $dsn = "$driver:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            try {
                self::$pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("❌ Database connection failed: " . $e->getMessage() . "\n");
            }
        }

        return self::$pdo;
    }
}
