<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Security\AuthGuard;
use App\Services\AdminService;
use App\Services\BookingService;
use App\Services\CourtService;
use InvalidArgumentException;
use Throwable;

final class AdminController
{
    public function __construct(
        private AuthGuard $guard,
        private AdminService $adminService,
        private CourtService $courts,
        private BookingService $bookings
    ) {
    }

    public function dashboard(Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $dashboard = $this->adminService->dashboard();
            JsonResponse::success($dashboard);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load dashboard.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function listCourts(Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $courts = $this->courts->list();
            JsonResponse::success(['courts' => $courts]);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load courts.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function createCourt(Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $court = $this->courts->create($request->body());
            JsonResponse::success(['court' => $court], 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to create court.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function deleteCourt(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $deleted = $this->courts->delete((int) $args['id']);
            if (!$deleted) {
                JsonResponse::error(['message' => 'Court not found.'], 404);
                return;
            }

            JsonResponse::success(['message' => 'Court deleted.']);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to delete court.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function listBookings(Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $status = $request->query('status');
            $bookings = $this->bookings->all(is_string($status) ? $status : null);
            JsonResponse::success(['bookings' => $bookings]);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load bookings.', 'details' => $exception->getMessage()], 500);
        }
    }

    public function createBooking(Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $booking = $this->bookings->createManual($request->body());
            JsonResponse::success(['booking' => $booking], 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to create booking.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function getCourt(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $court = $this->courts->getById((int) $args['id']);
            if ($court === null) {
                JsonResponse::error(['message' => 'Court not found.'], 404);
                return;
            }
            JsonResponse::success(['court' => $court]);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load court.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function updateCourt(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $updated = $this->courts->update((int) $args['id'], $request->body());
            if (!$updated) {
                JsonResponse::error(['message' => 'Court not found or no changes made.'], 404);
                return;
            }
            $court = $this->courts->getById((int) $args['id']);
            JsonResponse::success(['court' => $court, 'message' => 'Court updated successfully.']);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to update court.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function getBooking(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $booking = $this->bookings->getById((int) $args['id']);
            if ($booking === null) {
                JsonResponse::error(['message' => 'Booking not found.'], 404);
                return;
            }
            JsonResponse::success(['booking' => $booking]);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load booking.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function updateBooking(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $updated = $this->bookings->update((int) $args['id'], $request->body());
            if (!$updated) {
                JsonResponse::error(['message' => 'Booking not found or no changes made.'], 404);
                return;
            }
            $booking = $this->bookings->getById((int) $args['id']);
            JsonResponse::success(['booking' => $booking, 'message' => 'Booking updated successfully.']);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to update booking.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function cancelBooking(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $cancelled = $this->bookings->cancel((int) $args['id']);
            if (!$cancelled) {
                JsonResponse::error(['message' => 'Booking not found.'], 404);
                return;
            }
            JsonResponse::success(['message' => 'Booking cancelled successfully.']);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to cancel booking.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{id: string} $args
     */
    public function deleteBooking(array $args, Request $request): void
    {
        try {
            $this->guard->user($request, true);
            $deleted = $this->bookings->deleteBooking((int) $args['id']);
            if (!$deleted) {
                JsonResponse::error(['message' => 'Booking not found.'], 404);
                return;
            }
            JsonResponse::success(['message' => 'Booking deleted successfully.']);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to delete booking.', 'details' => $exception->getMessage()], 500);
        }
    }
}

