<?php
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/AdminRepository.php';

class AuthService
{
    private UserRepository $userRepository;
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->adminRepository = new AdminRepository();
    }

    public function loginUser(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !password_verify($password, $user->password_hash)) {
            throw new Exception('Invalid credentials');
        }

        $token = $this->generateToken();
        
        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->user_id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => 'user'
            ]
        ];
    }

    public function loginAdmin(string $username, string $password): array
    {
        $admin = $this->adminRepository->findByUsername($username);
        
        if (!$admin || !password_verify($password, $admin->password_hash)) {
            throw new Exception('Invalid admin credentials');
        }

        $token = $this->generateToken();
        
        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $admin->admin_id,
                'name' => $admin->username,
                'email' => $admin->username,
                'role' => 'admin'
            ]
        ];
    }

    public function registerUser(array $userData): array
    {
        // Check if user already exists
        if ($this->userRepository->findByEmail($userData['email'])) {
            throw new Exception('User with this email already exists');
        }

        if ($this->userRepository->findByPhoneNumber($userData['phone_number'])) {
            throw new Exception('User with this phone number already exists');
        }

        // Hash password
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);

        $user = $this->userRepository->create($userData);
        $token = $this->generateToken();

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->user_id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => 'user'
            ]
        ];
    }

    public function createAdmin(array $adminData): Admin
    {
        // Check if admin already exists
        if ($this->adminRepository->findByUsername($adminData['username'])) {
            throw new Exception('Admin with this username already exists');
        }

        // Hash password
        $adminData['password_hash'] = password_hash($adminData['password'], PASSWORD_DEFAULT);
        unset($adminData['password']);

        return $this->adminRepository->create($adminData);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function validateToken(string $token): bool
    {
        // For demo purposes, accept any non-empty token
        // In production, you'd validate against stored tokens
        return !empty($token);
    }
}