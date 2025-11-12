<?php
require_once __DIR__ . '/../../services/CourtService.php';
require_once __DIR__ . '/../../services/BookingService.php';

class AdminController
{
    private CourtService $courtService;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->courtService = new CourtService();
        $this->bookingService = new BookingService();
    }

    public function getDashboardStats(): void
    {
        try {
            $stats = $this->bookingService->getBookingStats();
            echo json_encode(['success' => true, 'stats' => $stats]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getAllBookings(): void
    {
        try {
            $bookings = $this->bookingService->getAllBookings();
            echo json_encode(['success' => true, 'bookings' => $bookings]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getTodaysBookings(): void
    {
        try {
            $bookings = $this->bookingService->getTodaysBookings();
            echo json_encode(['success' => true, 'bookings' => $bookings]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createCourt(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $requiredFields = ['court_name', 'court_type', 'location', 'price_per_hour'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field $field is required"]);
                    return;
                }
            }

            $result = $this->courtService->createCourt($data);
            http_response_code(201);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateCourt(int $courtId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $result = $this->courtService->updateCourt($courtId, $data);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteCourt(int $courtId): void
    {
        try {
            $result = $this->courtService->deleteCourt($courtId);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getAllCourts(): void
    {
        try {
            // Get all courts including inactive ones for admin
            $courts = $this->courtService->getAllCourts();
            echo json_encode(['success' => true, 'courts' => $courts]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function generateTimeSlots(int $courtId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $date = $data['date'] ?? date('Y-m-d');
            $options = [
                'start_time' => $data['start_time'] ?? '08:00',
                'end_time' => $data['end_time'] ?? '22:00',
                'duration' => $data['duration'] ?? 60
            ];

            $result = $this->courtService->generateTimeSlots($courtId, $date, $options);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
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
}