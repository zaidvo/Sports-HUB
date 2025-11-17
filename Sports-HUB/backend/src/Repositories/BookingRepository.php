<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BookingRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO bookings (user_id, court_id, customer_name, customer_email, customer_phone, booking_date, start_time, duration, total_price, status, notes)
             VALUES (:user_id, :court_id, :customer_name, :customer_email, :customer_phone, :booking_date, :start_time, :duration, :total_price, :status, :notes)'
        );

        $stmt->execute([
            'user_id' => $payload['user_id'],
            'court_id' => $payload['court_id'],
            'customer_name' => $payload['customer_name'],
            'customer_email' => $payload['customer_email'],
            'customer_phone' => $payload['customer_phone'],
            'booking_date' => $payload['booking_date'],
            'start_time' => $payload['start_time'],
            'duration' => $payload['duration'],
            'total_price' => $payload['total_price'],
            'status' => $payload['status'] ?? 'confirmed',
            'notes' => $payload['notes'] ?? null,
        ]);

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id) ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT b.*, c.name AS court_name, c.type AS court_type FROM bookings b
             LEFT JOIN courts c ON b.court_id = c.id
             WHERE b.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT b.*, c.name AS court_name, c.type AS court_type
             FROM bookings b
             INNER JOIN courts c ON b.court_id = c.id
             WHERE b.user_id = :user_id
             ORDER BY b.booking_date DESC, b.start_time DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?string $status = null): array
    {
        if ($status === null) {
            $stmt = $this->connection->query(
                'SELECT b.*, u.name AS user_name, c.name AS court_name, c.type AS court_type
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 LEFT JOIN courts c ON b.court_id = c.id
                 ORDER BY b.created_at DESC'
            );
            return $stmt->fetchAll();
        }

        $stmt = $this->connection->prepare(
            'SELECT b.*, u.name AS user_name, c.name AS court_name, c.type AS court_type
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN courts c ON b.court_id = c.id
             WHERE b.status = :status
             ORDER BY b.created_at DESC'
        );
        $stmt->execute(['status' => $status]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByCourtAndDate(int $courtId, string $date): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM bookings WHERE court_id = :court_id AND booking_date = :booking_date
             AND status IN (\'pending\', \'confirmed\') ORDER BY start_time ASC'
        );
        $stmt->execute([
            'court_id' => $courtId,
            'booking_date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    public function totalBookings(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) FROM bookings');
        $count = $stmt->fetchColumn();

        return (int) $count;
    }

    public function totalRevenue(): float
    {
        $stmt = $this->connection->query(
            "SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'confirmed'"
        );
        $sum = $stmt->fetchColumn();

        return (float) $sum;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 5): array
    {
        $stmt = $this->connection->prepare(
            'SELECT b.*, u.name AS user_name, c.name AS court_name
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN courts c ON b.court_id = c.id
             ORDER BY b.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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

        $sql = 'UPDATE bookings SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM bookings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

