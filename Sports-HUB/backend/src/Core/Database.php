<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private PDO $connection;

    public function __construct()
    {
        $dsn = Env::get('DB_DSN');

        if ($dsn === null) {
            $host = Env::get('DB_HOST');
            $port = Env::get('DB_PORT', '5432');
            $database = Env::get('DB_DATABASE');

            if ($host === null || $database === null) {
                throw new RuntimeException('Database DSN or host/database configuration is missing.');
            }

            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database);
        } elseif (str_starts_with($dsn, 'postgresql://')) {
            $dsn = $this->convertPostgresUrlToDsn($dsn);
        }

        $username = Env::get('DB_USERNAME');
        $password = Env::get('DB_PASSWORD');

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Failed to connect to the database: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function convertPostgresUrlToDsn(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            throw new RuntimeException('Invalid PostgreSQL connection URL.');
        }

        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? '5432';
        $path = $parts['path'] ?? '/';
        $database = ltrim($path, '/');
        $query = $parts['query'] ?? '';
        parse_str($query, $queryParams);

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database);

        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                $dsn .= sprintf(';%s=%s', $key, $value);
            }
        }

        return $dsn;
    }
}

