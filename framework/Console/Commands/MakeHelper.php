<?php

namespace Framework\Console\Commands;

class MakeHelper
{
    /**
     * Parse a class name into namespace, class name, directory, and file path.
     */
    public static function parseClassName(string $name, string $baseNamespace, string $basePath): array
    {
        $name = str_replace('\\', '/', $name);

        $path = $basePath . '/' . $name . '.php';
        $dir = dirname($path);

        $subNamespace = dirname($name);
        $subNamespace = $subNamespace === '.' ? '' : '\\' . str_replace('/', '\\', $subNamespace);
        $namespace = $baseNamespace . $subNamespace;

        $className = basename($name);

        return [$namespace, $className, $dir, $path];
    }

    /**
     * Generate a table name from a model name.
     */
    public static function tableNameFromModel(string $modelName): string
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
        return $snake . 's';
    }
}
