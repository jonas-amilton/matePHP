<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeMigrationCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:migration MigrationName\n";
            return;
        }

        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . $name . '.php';
        $path = __DIR__ . '/../../../database/migrations/' . $fileName;

        $template = <<<PHP
<?php

return new class {
    public function up(\$pdo)
    {
        // Migration logic here
    }

    public function down(\$pdo)
    {
        // Rollback logic here
    }
};
PHP;

        file_put_contents($path, $template);
        echo "Migration created: $path\n";
    }
}
