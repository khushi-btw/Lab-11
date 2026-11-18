<?php
// config/database.php
// Database configuration loaded from environment variables only.
// Never hardcode credentials here.

function getDatabaseConnection(): PDO
{
    $host     = getenv('DB_HOST')     ?: $_ENV['DB_HOST']     ?? 'localhost';
    $port     = getenv('DB_PORT')     ?: $_ENV['DB_PORT']     ?? '5432';
    $dbname   = getenv('DB_NAME')     ?: $_ENV['DB_NAME']     ?? 'lab11_db';
    $user     = getenv('DB_USER')     ?: $_ENV['DB_USER']     ?? 'postgres';
    $password = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? '';
    $sslmode  = getenv('DB_SSLMODE') ?: $_ENV['DB_SSLMODE']  ?? 'prefer';

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error without exposing credentials
        error_log('Database connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed. Please try again later.');
    }
}
