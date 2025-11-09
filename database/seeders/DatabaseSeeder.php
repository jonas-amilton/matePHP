<?php

namespace Database\Seeders;

use Framework\Core\Database;

class DatabaseSeeder
{
    public function run(): void
    {
        $pdo = Database::getConnection();

        // Call another seeders here
        (new UserSeeder())->run($pdo);

        echo "✅ Database seeded successfully!\n";
    }
}
