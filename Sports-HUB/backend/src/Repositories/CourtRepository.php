<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CourtRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $type = null): array
    {
        if ($type === null) {
            $stmt = $this->connection->query('SELECT * FROM courts ORDER BY created_at DESC');
            return $stmt->fetchAll();
        }

        // Case-insensitive search for type
        $stmt = $this->connection->prepare('SELECT * FROM courts WHERE LOWER(type) = LOWER(:type) ORDER BY created_at DESC');
        $stmt->execute(['type' => $type]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM courts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @param array{name: string, type: string, location: string, price_per_hour: float, status?: string, image_url?: string|null} $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO courts (name, type, location, price_per_hour, status, image_url) VALUES (:name, :type, :location, :price_per_hour, :status, :image_url)'
        );

        $stmt->execute([
            'name' => $payload['name'],
            'type' => $payload['type'],
            'location' => $payload['location'],
            'price_per_hour' => $payload['price_per_hour'],
            'status' => $payload['status'] ?? 'active',
            'image_url' => $payload['image_url'] ?? null,
        ]);

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id) ?? [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(int $id, array $payload): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($payload as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }

        $sql = 'UPDATE courts SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM courts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function totalCourts(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) FROM courts');
        $count = $stmt->fetchColumn();

        return (int) $count;
    }
}

