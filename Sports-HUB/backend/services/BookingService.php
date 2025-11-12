<?php
require_once __DIR__ . '/../repositories/BookingRepository.php';
require_once __DIR__ . '/../repositories/SlotRepository.php';
require_once __DIR__ . '/../repositories/CourtRepository.php';

class BookingService
{
    private BookingRepository $bookingRepository;
    private SlotRepository $slotRepository;
    private CourtRepository $courtRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository();
        $this->slotRepository = new SlotRepository();
        $this->courtRepository = new CourtRepository();
    }

    public function createBooking(array $bookingData): array
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Validate slot availability
            $slot = $this->slotRepository->findById($bookingData['slot_id']);
            if (!$slot) {
                throw new Exception('Slot not found');
            }

            if ($slot->is_booked) {
                throw new Exception('Slot is already booked');
            }

            // Get court details for pricing
            $court = $this->courtRepository->findById($slot->court_id);
            if (!$court) {
                throw new Exception('Court not found');
            }

            // Calculate duration and amount
            $startTime = new DateTime($slot->start_time);
            $endTime = new DateTime($slot->end_time);
            $duration = $endTime->diff($startTime)->h + ($endTime->diff($startTime)->i / 60);
            $amount = $court->price_per_hour * $duration;

            // Create booking
            $bookingData['amount_paid'] = $amount;
            $bookingData['court_id'] = $court->court_id;
            $bookingData['confirmation_message'] = $this->generateConfirmationMessage($court, $slot);

            $booking = $this->bookingRepository->create($bookingData);

            // Mark slot as booked
            $this->slotRepository->markAsBooked($bookingData['slot_id']);

            $pdo->commit();

            return [
                'success' => true,
                'booking' => $booking->toArray(),
                'message' => 'Booking created successfully'
            ];

        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    public function getUserBookings(int $userId): array
    {
        return $this->bookingRepository->findByUserId($userId);
    }

    public function getAllBookings(): array
    {
        return $this->bookingRepository->findAllWithDetails();
    }

    public function updateBooking(int $bookingId, array $updateData): array
    {
        $booking = $this->bookingRepository->update($bookingId, $updateData);
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        return [
            'success' => true,
            'booking' => $booking->toArray(),
            'message' => 'Booking updated successfully'
        ];
    }

    public function cancelBooking(int $bookingId): array
    {
        $booking = $this->bookingRepository->findById($bookingId);
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Update booking status
            $this->bookingRepository->update($bookingId, ['status' => 'Cancelled']);

            // Free up the slot
            $this->slotRepository->markAsAvailable($booking->slot_id);

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ];

        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    public function getTodaysBookings(): array
    {
        return $this->bookingRepository->getTodaysBookings();
    }

    public function getBookingStats(): array
    {
        $stats = $this->bookingRepository->getBookingStats();
        
        // Add active courts count
        $courts = $this->courtRepository->findAll();
        $stats['active_courts'] = count($courts);

        return $stats;
    }

    private function generateConfirmationMessage(Court $court, AvailableSlot $slot): string
    {
        return "Your booking for {$court->court_name} ({$court->court_type}) on {$slot->date} from {$slot->start_time} to {$slot->end_time} has been confirmed. Location: {$court->location}";
    }
}