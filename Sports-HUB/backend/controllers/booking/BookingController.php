<?php
require_once __DIR__ . '/../../services/BookingService.php';

class BookingController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    public function createBooking(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $requiredFields = ['user_id', 'slot_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field $field is required"]);
                    return;
                }
            }

            $result = $this->bookingService->createBooking($data);
            http_response_code(201);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getUserBookings(): void
    {
        try {
            $userId = $_GET['user_id'] ?? null;
            
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                return;
            }

            $bookings = $this->bookingService->getUserBookings((int)$userId);
            echo json_encode(['success' => true, 'bookings' => $bookings]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateBooking(int $bookingId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $result = $this->bookingService->updateBooking($bookingId, $data);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancelBooking(int $bookingId): void
    {
        try {
            $result = $this->bookingService->cancelBooking($bookingId);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getBookingById(int $bookingId): void
    {
        try {
            // Implementation would get booking details
            echo json_encode(['success' => true, 'message' => 'Get booking by ID - Not implemented yet']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}