<?php

namespace Database\Seeders;

use PDO;

class UserSeeder
{
    public function run(PDO $pdo)
    {
        $users = [
            ['name' => 'Jonas', 'email' => 'jonas@example.com'],
            ['name' => 'Maria', 'email' => 'maria@example.com'],
            ['name' => 'Carlos', 'email' => 'carlos@example.com'],
        ];

        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");

        foreach ($users as $user) {
            $stmt->execute($user);
        }

        echo "✅ Users seeded successfully!\n";
    }
}
