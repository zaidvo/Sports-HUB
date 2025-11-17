<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Services\AuthService;
use InvalidArgumentException;
use Throwable;

final class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function register(Request $request): void
    {
        try {
            $result = $this->auth->register($request->body());
            JsonResponse::success($result, 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Registration failed.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function login(Request $request): void
    {
        try {
            $result = $this->auth->login($request->body());
            JsonResponse::success($result);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 401);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Login failed.', 'details' => $exception->getMessage()], 500);
        }
    }
}

