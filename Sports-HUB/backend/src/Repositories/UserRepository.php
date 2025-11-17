<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @param array{name: string, email: string, phone: string, password: string, role?: string} $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, :role)'
        );

        $stmt->execute([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'password' => $payload['password'],
            'role' => $payload['role'] ?? 'user',
        ]);

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id) ?? [];
    }

    public function totalUsers(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) AS total FROM users');
        $count = $stmt->fetchColumn();

        return (int) $count;
    }
}

