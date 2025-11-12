<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/Admin.php';

class AdminRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findByUsername(string $username): ?Admin
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $data = $stmt->fetch();
        
        return $data ? new Admin($data) : null;
    }

    public function findById(int $id): ?Admin
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE admin_id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Admin($data) : null;
    }

    public function create(array $adminData): Admin
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admins (username, password_hash) 
             VALUES (?, ?) RETURNING *'
        );
        
        $stmt->execute([
            $adminData['username'],
            $adminData['password_hash']
        ]);
        
        $data = $stmt->fetch();
        return new Admin($data);
    }
}