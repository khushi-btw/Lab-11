<?php
// config/database.php
// Database configuration loaded from environment variables only.
// Never hardcode credentials here.

function loadLocalEnvironment(): void
{
    $envFile = dirname(__DIR__) . '/.env';
    if (!is_file($envFile)) return;

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

function getDatabaseConnection(): PDO
{
    loadLocalEnvironment();

    $env = static function (string $name, string $default = ''): string {
        $value = getenv($name);
        if ($value !== false && $value !== '') return $value;
        return (string)($_ENV[$name] ?? $default);
    };

    $databaseUrl = $env('DATABASE_URL');
    if ($databaseUrl !== '') {
        $url = parse_url($databaseUrl);
        if (
            $url === false
            || !in_array($url['scheme'] ?? '', ['postgres', 'postgresql'], true)
            || empty($url['host'])
            || empty($url['path'])
            || empty($url['user'])
        ) {
            throw new RuntimeException('Database connection settings are invalid.');
        }

        $query = [];
        parse_str($url['query'] ?? '', $query);
        $host = $url['host'];
        $port = (string)($url['port'] ?? 5432);
        $dbname = rawurldecode(ltrim($url['path'], '/'));
        $user = rawurldecode($url['user']);
        $password = rawurldecode($url['pass'] ?? '');
        $sslmode = is_string($query['sslmode'] ?? null) ? $query['sslmode'] : $env('DB_SSLMODE', 'require');
    } else {
        $host = $env('DB_HOST', 'localhost');
        $port = $env('DB_PORT', '5432');
        $dbname = $env('DB_NAME', 'lab11_db');
        $user = $env('DB_USER', 'postgres');
        $password = $env('DB_PASSWORD');
        $sslmode = $env('DB_SSLMODE', 'prefer');
    }

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
        // Log only the state code; PDO messages can include connection details.
        error_log('Database connection failed. SQLSTATE: ' . $e->getCode());
        throw new RuntimeException('Database connection failed. Please try again later.');
    }
}
