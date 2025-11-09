<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeModelCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:model ModelName\n";
            return;
        }

        [$namespace, $className, $dir, $path] =
            MakeHelper::parseClassName($name, 'App\Models', __DIR__ . '/../../../app/Models');

        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_exists($path)) {
            echo "Model already exists: $path\n";
            return;
        }

        $table = MakeHelper::tableNameFromModel($className);

        $template = <<<PHP
<?php
namespace $namespace;

use Framework\Core\Model;

class $className extends Model
{
    protected static string \$table = '$table';
    protected array \$fillable = [];
}
PHP;

        file_put_contents($path, $template);
        echo "Model created: $path (table: $table)\n";
    }
}
