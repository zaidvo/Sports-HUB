<?php

declare(strict_types=1);

namespace App\Security;

use App\Core\Env;
use InvalidArgumentException;

final class JwtService
{
    private string $secret;
    private string $issuer;
    private int $ttl;

    public function __construct()
    {
        $secret = Env::get('JWT_SECRET');
        if ($secret === null) {
            throw new InvalidArgumentException('JWT_SECRET is not configured.');
        }

        $this->secret = $secret;
        $this->issuer = Env::get('JWT_ISSUER', 'SportHub');
        $this->ttl = (int) Env::get('JWT_EXPIRES_IN', '3600');
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function generateToken(array $claims): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iss' => $this->issuer,
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ]);

        return \JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * @return array<string, mixed>
     */
    public function decodeToken(string $jwt): array
    {
        $decoded = \JWT::decode($jwt, $this->secret);

        return (array) $decoded;
    }
}

