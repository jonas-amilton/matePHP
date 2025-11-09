<?php


return new class {
    public function up(\PDO $pdo)
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }


    public function down(\PDO $pdo)
    {
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
