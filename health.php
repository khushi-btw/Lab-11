<?php
// health.php — Health check endpoint for Render deployment.
// Returns HTTP 200 with JSON payload if the app is running.
// Returns HTTP 503 if the database is unreachable.

require_once __DIR__ . '/config/database.php';

// Load .env for local development
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

header('Content-Type: application/json');

try {
    $pdo  = getDatabaseConnection();
    $stmt = $pdo->query("SELECT 1 AS ping");
    $row  = $stmt->fetch();

    if ($row && $row['ping'] === 1) {
        http_response_code(200);
        echo json_encode([
            'status'   => 'ok',
            'database' => 'connected',
            'app'      => 'ACCESS — Authentication Portal',
            'lab'      => 'LAB 11',
        ]);
    } else {
        throw new RuntimeException('Database ping failed.');
    }
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database unavailable',
    ]);
}
