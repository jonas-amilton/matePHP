<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeMiddlewareCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:middleware MiddlewareName\n";
            return;
        }

        [$namespace, $className, $dir, $path] =
            MakeHelper::parseClassName($name, 'App\Http\Middlewares', __DIR__ . '/../../../app/Http/Middlewares');

        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_exists($path)) {
            echo "Middleware already exists: $path\n";
            return;
        }

        $template = <<<PHP
<?php
namespace $namespace;

use Framework\Core\Request;

class $className
{
    public function handle(Request \$request): void
    {
        // Middleware logic here
    }
}
PHP;

        file_put_contents($path, $template);
        echo "Middleware created: $path\n";
    }
}
