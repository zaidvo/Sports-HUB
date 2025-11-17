<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    /**
     * @param array<mixed> $data
     */
    public static function success(array $data = [], int $status = 200): void
    {
        self::send($data, $status);
    }

    /**
     * @param array<string, mixed>|string $error
     */
    public static function error($error, int $status = 400): void
    {
        $payload = is_string($error) ? ['error' => $error] : $error;
        self::send($payload, $status);
    }

    /**
     * @param array<mixed> $payload
     */
    private static function send(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json');

        try {
            echo json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo '{"message":"Failed to encode response."}';
        }
    }
}

