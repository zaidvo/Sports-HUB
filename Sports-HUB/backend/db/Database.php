<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../config/config.php';
        $url = $config['database_url'];

        // Check if it's SQLite
        if (str_starts_with($url, 'sqlite:')) {
            $dbPath = str_replace('sqlite:', '', $url);
            // Create directory if it doesn't exist
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $dsn = $url;
            self::$pdo = new PDO($dsn);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Enable foreign keys for SQLite
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            
            return self::$pdo;
        }

        // Parse PostgreSQL DATABASE_URL
        $parts = parse_url($url);
        if ($parts === false) {
            throw new RuntimeException('Invalid DATABASE_URL');
        }

        $scheme = $parts['scheme'] ?? 'postgresql';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? 5432;
        $path = $parts['path'] ?? '/postgres';
        $dbname = ltrim($path, '/');

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $sslmode = $query['sslmode'] ?? 'require';

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=%s', $host, $port, $dbname, $sslmode);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;
    }
}
