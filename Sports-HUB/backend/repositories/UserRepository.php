<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function findByPhoneNumber(string $phone): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE phone_number = ?');
        $stmt->execute([$phone]);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function create(array $userData): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (full_name, phone_number, email, password_hash) 
             VALUES (?, ?, ?, ?) RETURNING *'
        );
        
        $stmt->execute([
            $userData['full_name'],
            $userData['phone_number'],
            $userData['email'],
            $userData['password_hash']
        ]);
        
        $data = $stmt->fetch();
        return new User($data);
    }

    public function update(int $id, array $userData): ?User
    {
        $fields = [];
        $values = [];
        
        foreach (['full_name', 'phone_number', 'email'] as $field) {
            if (isset($userData[$field])) {
                $fields[] = "$field = ?";
                $values[] = $userData[$field];
            }
        }
        
        if (empty($fields)) {
            return $this->findById($id);
        }
        
        $values[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = ? RETURNING *';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE user_id = ?');
        return $stmt->execute([$id]);
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = [];
        
        while ($data = $stmt->fetch()) {
            $users[] = new User($data);
        }
        
        return $users;
    }
}