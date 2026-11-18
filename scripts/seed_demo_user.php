<?php
// scripts/seed_demo_user.php
// Seeds the demo user into the database safely (no duplicates).
// Run from project root: php scripts/seed_demo_user.php

require_once __DIR__ . '/../config/database.php';

// Load .env if it exists (for local development)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// First, ensure the table exists
function ensureTableExists(PDO $pdo): void
{
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id            SERIAL PRIMARY KEY,
        username      VARCHAR(100) NOT NULL UNIQUE,
        password_hash TEXT         NOT NULL,
        created_at    TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )";
    $pdo->exec($sql);
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users (username)");
    echo "[OK] Table 'users' verified.\n";
}

// Seed a user only if they don't already exist
function seedUser(PDO $pdo, string $username, string $plainPassword): void
{
    $check = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $check->execute([':username' => $username]);

    if ($check->fetch()) {
        echo "[SKIP] User '{$username}' already exists — no duplicate created.\n";
        return;
    }

    $hash   = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $insert = $pdo->prepare(
        "INSERT INTO users (username, password_hash) VALUES (:username, :hash)"
    );
    $insert->execute([':username' => $username, ':hash' => $hash]);
    echo "[OK] Demo user '{$username}' created successfully.\n";
    echo "     Hash: {$hash}\n";
}

try {
    $pdo = getDatabaseConnection();
    ensureTableExists($pdo);
    seedUser($pdo, 'lab11student', 'Demo@12345');
    echo "\n[DONE] Database seeded and ready.\n";
} catch (Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
