<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Services\CourtService;
use InvalidArgumentException;
use Throwable;

final class CourtController
{
    public function __construct(
        private CourtService $courts
    ) {
    }

    public function index(Request $request): void
    {
        try {
            $type = $request->query('type');
            $courts = $this->courts->list(is_string($type) ? $type : null);
            JsonResponse::success(['courts' => $courts]);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load courts.', 'details' => $exception->getMessage()], 500);
        }
    }

    /**
     * @param array{ id: string } $args
     */
    public function slots(array $args, Request $request): void
    {
        try {
            $date = $request->query('date');
            if (!is_string($date)) {
                throw new InvalidArgumentException('date query parameter is required (YYYY-MM-DD).');
            }

            $slots = $this->courts->availableSlots((int) $args['id'], $date);
            JsonResponse::success(['slots' => $slots]);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::error(['message' => 'Failed to load slots.', 'details' => $exception->getMessage()], 500);
        }
    }
}

