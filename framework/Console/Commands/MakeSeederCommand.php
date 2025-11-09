<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeSeederCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:seeder SeederName\n";
            return;
        }

        $path = __DIR__ . '/../../../database/seeders/' . $name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_exists($path)) {
            echo "Seeder already exists: $path\n";
            return;
        }

        $template = <<<PHP
<?php
namespace Database\Seeders;

use PDO;

class $name
{
    public function run(PDO \$pdo)
    {
        // Seed logic here
    }
}
PHP;

        file_put_contents($path, $template);
        echo "Seeder created: $path\n";
    }
}
