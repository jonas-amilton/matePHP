<?php

namespace Framework\Console;

class Kernel
{
    protected static array $commands = [
        'migrate' => \Framework\Console\Commands\MigrateCommand::class,
        'seed'    => \Framework\Console\Commands\SeedCommand::class,
        'make:controller' => \Framework\Console\Commands\MakeControllerCommand::class,
        'make:model'      => \Framework\Console\Commands\MakeModelCommand::class,
        'make:seeder'     => \Framework\Console\Commands\MakeSeederCommand::class,
        'make:migration'  => \Framework\Console\Commands\MakeMigrationCommand::class,
        // TODO: adicionar comandos para make:middleware, make:request, make:job, etc...
    ];

    public static function handle(array $argv)
    {
        $cmd = $argv[1] ?? null;

        if (!$cmd || !isset(self::$commands[$cmd])) {
            echo "Available commands: " . implode(', ', array_keys(self::$commands)) . "\n";
            return;
        }

        $commandClass = self::$commands[$cmd];
        $command = new $commandClass();
        $command->handle(array_slice($argv, 2));
    }
}
