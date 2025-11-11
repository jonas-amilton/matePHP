<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeControllerCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:controller ControllerName\n";
            return;
        }

        [$namespace, $className, $dir, $path] =
            MakeHelper::parseClassName($name, 'App\Http\Controllers', __DIR__ . '/../../../app/Http/Controllers');

        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_exists($path)) {
            echo "Controller already exists: $path\n";
            return;
        }

        $template = <<<PHP
<?php
namespace $namespace;

class $className extends Controller
{
    public function index()
    {
        //
    }
}
PHP;

        file_put_contents($path, $template);
        echo "Controller created: $path\n";
    }
}