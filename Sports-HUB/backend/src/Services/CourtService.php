<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;

final class CourtService
{
    private int $slotStartHour;
    private int $slotEndHour;
    private int $slotIntervalMinutes;

    public function __construct(
        private CourtRepository $courts,
        private BookingRepository $bookings
    ) {
        $this->slotStartHour = (int) Env::get('SLOT_START_HOUR', '6');
        $this->slotEndHour = (int) Env::get('SLOT_END_HOUR', '22');
        $this->slotIntervalMinutes = (int) Env::get('SLOT_INTERVAL_MINUTES', '60');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(?string $type = null): array
    {
        return $this->courts->all($type);
    }

    /**
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $required = ['name', 'type', 'location', 'price_per_hour'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("{$field} is required.");
            }
        }

        $price = (float) $data['price_per_hour'];
        if ($price <= 0) {
            throw new InvalidArgumentException('price_per_hour must be greater than zero.');
        }

        return $this->courts->create([
            'name' => (string) $data['name'],
            'type' => (string) $data['type'],
            'location' => (string) $data['location'],
            'price_per_hour' => $price,
            'status' => $data['status'] ?? 'active',
            'image_url' => $data['image_url'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->courts->delete($id);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function availableSlots(int $courtId, string $date): array
    {
        $dateObject = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($dateObject === false) {
            throw new InvalidArgumentException('Invalid date format. Use YYYY-MM-DD.');
        }

        $existingBookings = $this->bookings->findByCourtAndDate($courtId, $date);
        $slots = [];

        $current = $dateObject->setTime($this->slotStartHour, 0);
        $end = $dateObject->setTime($this->slotEndHour, 0);
        $interval = new DateInterval(sprintf('PT%dM', $this->slotIntervalMinutes));

        while ($current < $end) {
            $slotEnd = $current->add($interval);
            $isAvailable = true;

            foreach ($existingBookings as $booking) {
                $bookingStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', sprintf('%s %s', $date, $booking['start_time']));
                if ($bookingStart === false) {
                    continue;
                }

                $bookingEnd = $bookingStart->add(new DateInterval(sprintf('PT%dH', (int) $booking['duration'])));

                if ($this->timeRangesOverlap($current, $slotEnd, $bookingStart, $bookingEnd)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start_time' => $current->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                ];
            }

            $current = $slotEnd;
        }

        return $slots;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array
    {
        return $this->courts->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $court = $this->courts->findById($id);
        if ($court === null) {
            throw new InvalidArgumentException('Court not found.');
        }

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = (string) $data['name'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = (string) $data['type'];
        }
        if (isset($data['location'])) {
            $updateData['location'] = (string) $data['location'];
        }
        if (isset($data['price_per_hour'])) {
            $price = (float) $data['price_per_hour'];
            if ($price <= 0) {
                throw new InvalidArgumentException('price_per_hour must be greater than zero.');
            }
            $updateData['price_per_hour'] = $price;
        }
        if (isset($data['status'])) {
            $updateData['status'] = (string) $data['status'];
        }
        if (isset($data['image_url'])) {
            $updateData['image_url'] = $data['image_url'];
        }

        if (empty($updateData)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        return $this->courts->update($id, $updateData);
    }

    public function totalCourts(): int
    {
        return $this->courts->totalCourts();
    }

    private function timeRangesOverlap(
        DateTimeImmutable $startA,
        DateTimeImmutable $endA,
        DateTimeImmutable $startB,
        DateTimeImmutable $endB
    ): bool {
        return $startA < $endB && $startB < $endA;
    }
}

