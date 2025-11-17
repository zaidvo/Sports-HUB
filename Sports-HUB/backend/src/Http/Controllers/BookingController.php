<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Security\AuthGuard;
use App\Services\BookingService;
use InvalidArgumentException;
use Throwable;

final class BookingController
{
    public function __construct(
        private BookingService $bookings,
        private AuthGuard $guard
    ) {
    }

    public function index(Request $request): void
    {
        try {
            $user = $this->guard->user($request);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 401);
            return;
        }

        try {
            $bookings = $this->bookings->forUser((int) $user['id']);
            JsonResponse::success(['bookings' => $bookings]);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load bookings.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function store(Request $request): void
    {
        try {
            $user = $this->guard->user($request);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 401);
            return;
        }

        try {
            $booking = $this->bookings->create($request->body(), $user);
            JsonResponse::success(['booking' => $booking], 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to create booking.', 'details' => $exception->getMessage()], 500);
        }
    }
}

