<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use App\Repositories\UserRepository;

final class AdminService
{
    public function __construct(
        private UserRepository $users,
        private CourtRepository $courts,
        private BookingRepository $bookings
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return [
            'totals' => [
                'users' => $this->users->totalUsers(),
                'courts' => $this->courts->totalCourts(),
                'bookings' => $this->bookings->totalBookings(),
                'revenue' => $this->bookings->totalRevenue(),
            ],
            'recent_bookings' => $this->bookings->recent(),
        ];
    }
}

