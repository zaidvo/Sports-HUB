<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\Request;
use App\Repositories\UserRepository;
use InvalidArgumentException;
use Throwable;

final class AuthGuard
{
    public function __construct(
        private JwtService $jwt,
        private UserRepository $users
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function user(Request $request, bool $requireAdmin = false): array
    {
        $authorization = $request->header('Authorization');

        if (!is_string($authorization) || stripos($authorization, 'Bearer ') !== 0) {
            throw new InvalidArgumentException('Authorization header missing or malformed.');
        }

        $token = trim(substr($authorization, 7));
        if ($token === '') {
            throw new InvalidArgumentException('Missing bearer token.');
        }

        try {
            $payload = $this->jwt->decodeToken($token);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException('Invalid token: ' . $exception->getMessage(), 0, $exception);
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid token payload.');
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new InvalidArgumentException('User not found.');
        }

        if ($requireAdmin && ($user['role'] ?? 'user') !== 'admin') {
            throw new InvalidArgumentException('Admin privileges required.');
        }

        unset($user['password']);

        return $user;
    }
}

