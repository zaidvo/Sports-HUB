<?php
require_once __DIR__ . '/../../services/CourtService.php';

class CourtController
{
    private CourtService $courtService;

    public function __construct()
    {
        $this->courtService = new CourtService();
    }

    public function getAllCourts(): void
    {
        try {
            $filters = [];
            
            // Get query parameters for filtering
            if (isset($_GET['type'])) {
                $filters['type'] = $_GET['type'];
            }
            
            if (isset($_GET['location'])) {
                $filters['location'] = $_GET['location'];
            }
            
            if (isset($_GET['max_price'])) {
                $filters['max_price'] = (float)$_GET['max_price'];
            }

            $courts = $this->courtService->getAllCourts($filters);
            echo json_encode(['success' => true, 'courts' => $courts]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getCourtById(int $courtId): void
    {
        try {
            $court = $this->courtService->getCourtById($courtId);
            
            if (!$court) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Court not found']);
                return;
            }

            echo json_encode(['success' => true, 'court' => $court]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getAvailableSlots(int $courtId): void
    {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            
            $slots = $this->courtService->getAvailableSlots($courtId, $date);
            echo json_encode(['success' => true, 'slots' => $slots]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function searchCourts(): void
    {
        try {
            $criteria = [];
            
            if (isset($_GET['q'])) {
                $criteria['name'] = $_GET['q'];
            }
            
            if (isset($_GET['type'])) {
                $criteria['type'] = $_GET['type'];
            }
            
            if (isset($_GET['location'])) {
                $criteria['location'] = $_GET['location'];
            }
            
            if (isset($_GET['max_price'])) {
                $criteria['max_price'] = $_GET['max_price'];
            }

            $courts = $this->courtService->searchCourts($criteria);
            echo json_encode(['success' => true, 'courts' => $courts]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getCourtTypes(): void
    {
        try {
            $types = $this->courtService->getCourtTypes();
            echo json_encode(['success' => true, 'types' => $types]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getLocations(): void
    {
        try {
            $locations = $this->courtService->getLocations();
            echo json_encode(['success' => true, 'locations' => $locations]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}