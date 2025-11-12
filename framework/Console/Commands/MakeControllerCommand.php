<?php

namespace Framework\Console\Commands;

use Framework\Console\Command;

class MakeControllerCommand implements Command
{
    public function handle(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: php cli.php make:controller ControllerName [--resource|-r]\n";
            return;
        }

        $isResource = in_array('--resource', $args, true) || in_array('-r', $args, true);

        [$namespace, $className, $dir, $path] =
            MakeHelper::parseClassName($name, 'App\Http\Controllers', __DIR__ . '/../../../app/Http/Controllers');

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (file_exists($path)) {
            echo "Controller already exists: $path\n";
            return;
        }

        $templateHeader = <<<PHP
<?php
namespace $namespace;

class $className extends Controller
{
PHP;

        $templateFooter = <<<PHP
}
PHP;

        $methods = $isResource ? $this->resourceMethods() : $this->defaultMethods();
        $template = $templateHeader . "\n" . $methods . "\n" . $templateFooter;

        file_put_contents($path, $template);
        echo "Controller created: $path\n";

        $routesFile = __DIR__ . '/../../../routes/web.php';
        if ($isResource && is_writable($routesFile)) {
            $shortName = $this->resourceRouteName($className);
            $routeLine = "Route::resource('$shortName', \\$namespace\\$className::class);\n";
            $current = file_get_contents($routesFile);
            if (!str_contains($current, $routeLine)) {
                file_put_contents($routesFile, $current . PHP_EOL . $routeLine);
                echo "Resource route appended to routes/web.php\n";
            }
        }
    }

    private function defaultMethods(): string
    {
        return <<<PHP
    public function index()
    {
        //
    }
PHP;
    }

    private function resourceMethods(): string
    {
        return <<<'PHP'
    public function index()
    {
     //
    }

    public function create()
    {
     //
    }

    public function store(\Framework\Core\Request $request)
    {
     //
    }

    public function show($id)
    {
     //
    }

    public function edit($id)
    {
     //
    }

    public function update(\Framework\Core\Request $request, $id)
    {
     //
    }
    
    public function destroy($id)
    {
     //
    }
PHP;
    }

    private function resourceRouteName(string $className): string
    {
        $name = preg_replace('/Controller$/', '', $className);
        $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        if (substr($snake, -1) !== 's') $snake .= 's';
        return $snake;
    }
}