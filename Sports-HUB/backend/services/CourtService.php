<?php
require_once __DIR__ . '/../repositories/CourtRepository.php';
require_once __DIR__ . '/../repositories/SlotRepository.php';

class CourtService
{
    private CourtRepository $courtRepository;
    private SlotRepository $slotRepository;

    public function __construct()
    {
        $this->courtRepository = new CourtRepository();
        $this->slotRepository = new SlotRepository();
    }

    public function getAllCourts(array $filters = []): array
    {
        $courts = $this->courtRepository->findAll($filters);
        
        return array_map(function($court) {
            return $court->toArray();
        }, $courts);
    }

    public function getCourtById(int $courtId): ?array
    {
        $court = $this->courtRepository->findById($courtId);
        return $court ? $court->toArray() : null;
    }

    public function createCourt(array $courtData): array
    {
        $court = $this->courtRepository->create($courtData);
        
        return [
            'success' => true,
            'court' => $court->toArray(),
            'message' => 'Court created successfully'
        ];
    }

    public function updateCourt(int $courtId, array $courtData): array
    {
        $court = $this->courtRepository->update($courtId, $courtData);
        
        if (!$court) {
            throw new Exception('Court not found');
        }

        return [
            'success' => true,
            'court' => $court->toArray(),
            'message' => 'Court updated successfully'
        ];
    }

    public function deleteCourt(int $courtId): array
    {
        $success = $this->courtRepository->delete($courtId);
        
        if (!$success) {
            throw new Exception('Court not found');
        }

        return [
            'success' => true,
            'message' => 'Court deleted successfully'
        ];
    }

    public function getAvailableSlots(int $courtId, string $date): array
    {
        $slots = $this->slotRepository->findAvailableSlots($courtId, $date);
        
        return array_map(function($slot) {
            return $slot->toArray();
        }, $slots);
    }

    public function getAllSlots(int $courtId, string $date): array
    {
        $slots = $this->slotRepository->findByCourtAndDate($courtId, $date);
        
        return array_map(function($slot) {
            return $slot->toArray();
        }, $slots);
    }

    public function generateTimeSlots(int $courtId, string $date, array $options = []): array
    {
        $startTime = $options['start_time'] ?? '08:00';
        $endTime = $options['end_time'] ?? '22:00';
        $slotDuration = $options['duration'] ?? 60; // minutes

        $timeSlots = [];
        $current = new DateTime($date . ' ' . $startTime);
        $end = new DateTime($date . ' ' . $endTime);

        while ($current < $end) {
            $nextSlot = clone $current;
            $nextSlot->add(new DateInterval('PT' . $slotDuration . 'M'));
            
            if ($nextSlot <= $end) {
                $timeSlots[] = [
                    'start_time' => $current->format('H:i'),
                    'end_time' => $nextSlot->format('H:i')
                ];
            }
            
            $current = $nextSlot;
        }

        // Create slots in database
        $createdSlots = $this->slotRepository->generateSlotsForCourt($courtId, $date, $timeSlots);

        return [
            'success' => true,
            'slots_created' => count($createdSlots),
            'slots' => array_map(function($slot) {
                return $slot->toArray();
            }, $createdSlots)
        ];
    }

    public function getCourtTypes(): array
    {
        return $this->courtRepository->getCourtTypes();
    }

    public function getLocations(): array
    {
        return $this->courtRepository->getLocations();
    }

    public function searchCourts(array $criteria): array
    {
        $filters = [];

        if (!empty($criteria['type'])) {
            $filters['type'] = $criteria['type'];
        }

        if (!empty($criteria['location'])) {
            $filters['location'] = $criteria['location'];
        }

        if (!empty($criteria['max_price'])) {
            $filters['max_price'] = $criteria['max_price'];
        }

        return $this->getAllCourts($filters);
    }
}