<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use InvalidArgumentException;

final class BookingService
{
    public function __construct(
        private BookingRepository $bookings,
        private CourtRepository $courts
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function create(array $data, array $user): array
    {
        $required = ['court_id', 'booking_date', 'start_time', 'duration'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("{$field} is required.");
            }
        }

        $courtId = (int) $data['court_id'];
        $court = $this->courts->findById($courtId);
        if ($court === null) {
            throw new InvalidArgumentException('Court not found.');
        }

        $duration = (int) $data['duration'];
        if ($duration <= 0) {
            throw new InvalidArgumentException('Duration must be greater than zero.');
        }

        $startTime = $this->normalizeTime((string) $data['start_time']);
        $bookingDate = (string) $data['booking_date'];

        if (!$this->isSlotAvailable($courtId, $bookingDate, $startTime, $duration)) {
            throw new InvalidArgumentException('Selected time slot is not available.');
        }

        $totalPrice = (float) $court['price_per_hour'] * $duration;

        return $this->bookings->create([
            'user_id' => $user['id'],
            'court_id' => $courtId,
            'customer_name' => $data['customer_name'] ?? $user['name'],
            'customer_email' => $data['customer_email'] ?? $user['email'],
            'customer_phone' => $data['customer_phone'] ?? $user['phone'],
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'duration' => $duration,
            'total_price' => $totalPrice,
            'status' => $data['status'] ?? 'confirmed',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createManual(array $data): array
    {
        $required = ['court_id', 'booking_date', 'start_time', 'duration', 'customer_name', 'customer_email', 'customer_phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("{$field} is required.");
            }
        }

        $courtId = (int) $data['court_id'];
        $court = $this->courts->findById($courtId);
        if ($court === null) {
            throw new InvalidArgumentException('Court not found.');
        }

        $duration = (int) $data['duration'];
        if ($duration <= 0) {
            throw new InvalidArgumentException('Duration must be greater than zero.');
        }

        $startTime = $this->normalizeTime((string) $data['start_time']);
        $bookingDate = (string) $data['booking_date'];

        if (!$this->isSlotAvailable($courtId, $bookingDate, $startTime, $duration)) {
            throw new InvalidArgumentException('Selected time slot is not available.');
        }

        $totalPrice = $data['total_price'] ?? ((float) $court['price_per_hour'] * $duration);

        return $this->bookings->create([
            'user_id' => $data['user_id'] ?? null,
            'court_id' => $courtId,
            'customer_name' => (string) $data['customer_name'],
            'customer_email' => (string) $data['customer_email'],
            'customer_phone' => (string) $data['customer_phone'],
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'duration' => $duration,
            'total_price' => (float) $totalPrice,
            'status' => $data['status'] ?? 'confirmed',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        return $this->bookings->findByUser($userId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $status = null): array
    {
        return $this->bookings->findAll($status);
    }

    public function totalBookings(): int
    {
        return $this->bookings->totalBookings();
    }

    public function totalRevenue(): float
    {
        return $this->bookings->totalRevenue();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 5): array
    {
        return $this->bookings->recent($limit);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array
    {
        return $this->bookings->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $booking = $this->bookings->findById($id);
        if ($booking === null) {
            throw new InvalidArgumentException('Booking not found.');
        }

        $updateData = [];

        // Check if rescheduling is needed
        $needsSlotCheck = false;
        $courtId = $booking['court_id'];
        $bookingDate = $booking['booking_date'];
        $startTime = $booking['start_time'];
        $duration = (int) $booking['duration'];

        if (isset($data['court_id'])) {
            $courtId = (int) $data['court_id'];
            $court = $this->courts->findById($courtId);
            if ($court === null) {
                throw new InvalidArgumentException('Court not found.');
            }
            $updateData['court_id'] = $courtId;
            $needsSlotCheck = true;
        }

        if (isset($data['booking_date'])) {
            $bookingDate = (string) $data['booking_date'];
            $updateData['booking_date'] = $bookingDate;
            $needsSlotCheck = true;
        }

        if (isset($data['start_time'])) {
            $startTime = $this->normalizeTime((string) $data['start_time']);
            $updateData['start_time'] = $startTime;
            $needsSlotCheck = true;
        }

        if (isset($data['duration'])) {
            $duration = (int) $data['duration'];
            if ($duration <= 0) {
                throw new InvalidArgumentException('Duration must be greater than zero.');
            }
            $updateData['duration'] = $duration;
            $needsSlotCheck = true;

            // Recalculate price if duration changes
            $court = $this->courts->findById($courtId);
            if ($court !== null) {
                $updateData['total_price'] = (float) $court['price_per_hour'] * $duration;
            }
        }

        if (isset($data['status'])) {
            $updateData['status'] = (string) $data['status'];
        }

        if (isset($data['customer_name'])) {
            $updateData['customer_name'] = (string) $data['customer_name'];
        }

        if (isset($data['customer_email'])) {
            $updateData['customer_email'] = (string) $data['customer_email'];
        }

        if (isset($data['customer_phone'])) {
            $updateData['customer_phone'] = (string) $data['customer_phone'];
        }

        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        if (isset($data['total_price'])) {
            $updateData['total_price'] = (float) $data['total_price'];
        }

        if (empty($updateData)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        // Check slot availability if scheduling details changed
        if ($needsSlotCheck) {
            if (!$this->isSlotAvailableForUpdate($id, $courtId, $bookingDate, $startTime, $duration)) {
                throw new InvalidArgumentException('Selected time slot is not available.');
            }
        }

        return $this->bookings->update($id, $updateData);
    }

    public function cancel(int $id): bool
    {
        $booking = $this->bookings->findById($id);
        if ($booking === null) {
            throw new InvalidArgumentException('Booking not found.');
        }

        return $this->bookings->update($id, ['status' => 'cancelled']);
    }

    public function deleteBooking(int $id): bool
    {
        $booking = $this->bookings->findById($id);
        if ($booking === null) {
            throw new InvalidArgumentException('Booking not found.');
        }

        return $this->bookings->delete($id);
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        $date = date_create($time);
        if ($date === false) {
            throw new InvalidArgumentException('Invalid time format.');
        }

        return $date->format('H:i:s');
    }

    private function isSlotAvailable(int $courtId, string $date, string $startTime, int $durationHours): bool
    {
        $bookings = $this->bookings->findByCourtAndDate($courtId, $date);

        $requestedStart = strtotime("{$date} {$startTime}");
        $requestedEnd = strtotime("+{$durationHours} hour", $requestedStart);

        foreach ($bookings as $booking) {
            $existingStart = strtotime("{$booking['booking_date']} {$booking['start_time']}");
            $existingEnd = strtotime("+{$booking['duration']} hour", $existingStart);

            if ($requestedStart < $existingEnd && $existingStart < $requestedEnd) {
                return false;
            }
        }

        return true;
    }

    private function isSlotAvailableForUpdate(int $bookingId, int $courtId, string $date, string $startTime, int $durationHours): bool
    {
        $bookings = $this->bookings->findByCourtAndDate($courtId, $date);

        $requestedStart = strtotime("{$date} {$startTime}");
        $requestedEnd = strtotime("+{$durationHours} hour", $requestedStart);

        foreach ($bookings as $booking) {
            // Skip the current booking being updated
            if ((int) $booking['id'] === $bookingId) {
                continue;
            }

            $existingStart = strtotime("{$booking['booking_date']} {$booking['start_time']}");
            $existingEnd = strtotime("+{$booking['duration']} hour", $existingStart);

            if ($requestedStart < $existingEnd && $existingStart < $requestedEnd) {
                return false;
            }
        }

        return true;
    }
}

