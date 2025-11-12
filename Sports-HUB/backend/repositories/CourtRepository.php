<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/Court.php';

class CourtRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT * FROM courts WHERE is_active = true';
        $params = [];

        if (isset($filters['type'])) {
            $sql .= ' AND court_type = ?';
            $params[] = $filters['type'];
        }

        if (isset($filters['location'])) {
            $sql .= ' AND location ILIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }

        if (isset($filters['max_price'])) {
            $sql .= ' AND price_per_hour <= ?';
            $params[] = $filters['max_price'];
        }

        $sql .= ' ORDER BY court_name';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $courts = [];
        while ($data = $stmt->fetch()) {
            $courts[] = new Court($data);
        }
        
        return $courts;
    }

    public function findById(int $id): ?Court
    {
        $stmt = $this->pdo->prepare('SELECT * FROM courts WHERE court_id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Court($data) : null;
    }

    public function create(array $courtData): Court
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO courts (court_name, court_type, location, price_per_hour, description, image_url) 
             VALUES (?, ?, ?, ?, ?, ?) RETURNING *'
        );
        
        $stmt->execute([
            $courtData['court_name'],
            $courtData['court_type'],
            $courtData['location'],
            $courtData['price_per_hour'],
            $courtData['description'] ?? null,
            $courtData['image_url'] ?? null
        ]);
        
        $data = $stmt->fetch();
        return new Court($data);
    }

    public function update(int $id, array $courtData): ?Court
    {
        $fields = [];
        $values = [];
        
        $allowedFields = ['court_name', 'court_type', 'location', 'price_per_hour', 'description', 'image_url', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($courtData[$field])) {
                $fields[] = "$field = ?";
                $values[] = $courtData[$field];
            }
        }
        
        if (empty($fields)) {
            return $this->findById($id);
        }
        
        $values[] = $id;
        $sql = 'UPDATE courts SET ' . implode(', ', $fields) . ' WHERE court_id = ? RETURNING *';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        $data = $stmt->fetch();
        
        return $data ? new Court($data) : null;
    }

    public function delete(int $id): bool
    {
        // Soft delete - mark as inactive
        $stmt = $this->pdo->prepare('UPDATE courts SET is_active = false WHERE court_id = ?');
        return $stmt->execute([$id]);
    }

    public function getCourtTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT court_type FROM courts WHERE is_active = true ORDER BY court_type');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getLocations(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT location FROM courts WHERE is_active = true ORDER BY location');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}