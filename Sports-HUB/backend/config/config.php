<?php
// Loads environment variables from .env (simple implementation)
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"');
        $_ENV[$key] = $value;
    }
}

$config = [
    'database_url' => $_ENV['DATABASE_URL'] ?? 'postgresql://localhost:5432/sportzhub',
    'cors_allowed_origin' => $_ENV['CORS_ALLOWED_ORIGIN'] ?? '*',
];

return $config;