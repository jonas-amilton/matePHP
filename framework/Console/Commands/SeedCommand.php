<?php

namespace Framework\Console\Commands;

use Database\Seeders\DatabaseSeeder;

class SeedCommand implements \Framework\Console\Command
{
    public function handle(array $args): void
    {
        (new DatabaseSeeder())->run();
    }
}
