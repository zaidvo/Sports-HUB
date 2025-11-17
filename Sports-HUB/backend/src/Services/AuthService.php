<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Security\JwtService;
use InvalidArgumentException;

final class AuthService
{
    public function __construct(
        private UserRepository $users,
        private JwtService $jwt
    ) {
    }

    /**
     * Simple registration - no password hashing
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        // Simple validation
        if (empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Email and password are required.');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        if ($this->users->findByEmail($data['email']) !== null) {
            throw new InvalidArgumentException('Email already registered.');
        }

        // Create user with plain text password
        $user = $this->users->create([
            'name' => (string) ($data['name'] ?? $data['email']),
            'email' => strtolower((string) $data['email']),
            'phone' => (string) ($data['phone'] ?? '0000000000'),
            'password' => (string) $data['password'], // Plain text - no hashing
            'role' => isset($data['role']) && $data['role'] === 'admin' ? 'admin' : 'user',
        ]);

        $token = $this->jwt->generateToken([
            'sub' => $user['id'],
            'role' => $user['role'],
        ]);

        unset($user['password']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Simple login - plain text password comparison
     * @param array<string, mixed> $credentials
     * @return array<string, mixed>
     */
    public function login(array $credentials): array
    {
        $email = strtolower(trim((string) ($credentials['email'] ?? '')));
        $password = (string) ($credentials['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Email and password are required.');
        }

        $user = $this->users->findByEmail($email);

        // Simple plain text password comparison
        if ($user === null || $user['password'] !== $password) {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        $token = $this->jwt->generateToken([
            'sub' => $user['id'],
            'role' => $user['role'],
        ]);

        unset($user['password']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUserById(int $userId): ?array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return null;
        }

        unset($user['password']);

        return $user;
    }
}

