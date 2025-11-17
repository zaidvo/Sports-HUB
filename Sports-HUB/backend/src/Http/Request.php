<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    private array $body;

    public function __construct()
    {
        $this->body = $this->parseJsonBody();
    }

    /**
     * @return array<mixed>
     */
    private function parseJsonBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') === false) {
            return [];
        }

        $input = file_get_contents('php://input');
        if ($input === false || $input === '') {
            return [];
        }

        $decoded = json_decode($input, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @return array<mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$serverKey] ?? $default;
    }
}

