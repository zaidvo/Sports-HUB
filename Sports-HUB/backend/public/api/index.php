<?php
// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include autoloader and dependencies
require_once __DIR__ . '/../../controllers/user/AuthController.php';
require_once __DIR__ . '/../../controllers/user/CourtController.php';
require_once __DIR__ . '/../../controllers/booking/BookingController.php';
require_once __DIR__ . '/../../controllers/admin/AdminController.php';

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string and API prefix
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/backend/public/api', '', $path);
$path = trim($path, '/');

// Split path into segments
$segments = explode('/', $path);

try {
    // Route the request
    switch ($segments[0]) {
        case 'auth':
            handleAuthRoutes($segments, $requestMethod);
            break;
            
        case 'courts':
            handleCourtRoutes($segments, $requestMethod);
            break;
            
        case 'bookings':
            handleBookingRoutes($segments, $requestMethod);
            break;
            
        case 'admin':
            handleAdminRoutes($segments, $requestMethod);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}

function handleAuthRoutes($segments, $method) {
    $controller = new AuthController();
    
    switch ($segments[1] ?? '') {
        case 'login':
            if ($method === 'POST') {
                $controller->login();
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'register':
            if ($method === 'POST') {
                $controller->register();
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'logout':
            if ($method === 'POST') {
                $controller->logout();
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'create-admin':
            if ($method === 'POST') {
                $controller->createAdmin();
            } else {
                methodNotAllowed();
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Auth endpoint not found']);
    }
}

function handleCourtRoutes($segments, $method) {
    $controller = new CourtController();
    
    if (count($segments) === 1) {
        // /courts
        switch ($method) {
            case 'GET':
                $controller->getAllCourts();
                break;
            default:
                methodNotAllowed();
        }
    } elseif (is_numeric($segments[1])) {
        // /courts/{id}
        $courtId = (int)$segments[1];
        
        if (isset($segments[2])) {
            // /courts/{id}/slots
            if ($segments[2] === 'slots' && $method === 'GET') {
                $controller->getAvailableSlots($courtId);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Court endpoint not found']);
            }
        } else {
            // /courts/{id}
            switch ($method) {
                case 'GET':
                    $controller->getCourtById($courtId);
                    break;
                default:
                    methodNotAllowed();
            }
        }
    } else {
        // Other court endpoints
        switch ($segments[1]) {
            case 'search':
                if ($method === 'GET') {
                    $controller->searchCourts();
                } else {
                    methodNotAllowed();
                }
                break;
                
            case 'types':
                if ($method === 'GET') {
                    $controller->getCourtTypes();
                } else {
                    methodNotAllowed();
                }
                break;
                
            case 'locations':
                if ($method === 'GET') {
                    $controller->getLocations();
                } else {
                    methodNotAllowed();
                }
                break;
                
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Court endpoint not found']);
        }
    }
}

function handleBookingRoutes($segments, $method) {
    $controller = new BookingController();
    
    if (count($segments) === 1) {
        // /bookings
        switch ($method) {
            case 'POST':
                $controller->createBooking();
                break;
            case 'GET':
                $controller->getUserBookings();
                break;
            default:
                methodNotAllowed();
        }
    } elseif (is_numeric($segments[1])) {
        // /bookings/{id}
        $bookingId = (int)$segments[1];
        
        if (isset($segments[2]) && $segments[2] === 'cancel') {
            // /bookings/{id}/cancel
            if ($method === 'PUT' || $method === 'POST') {
                $controller->cancelBooking($bookingId);
            } else {
                methodNotAllowed();
            }
        } else {
            // /bookings/{id}
            switch ($method) {
                case 'GET':
                    $controller->getBookingById($bookingId);
                    break;
                case 'PUT':
                    $controller->updateBooking($bookingId);
                    break;
                default:
                    methodNotAllowed();
            }
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking endpoint not found']);
    }
}

function handleAdminRoutes($segments, $method) {
    $controller = new AdminController();
    
    switch ($segments[1] ?? '') {
        case 'dashboard':
            if ($method === 'GET') {
                $controller->getDashboardStats();
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'courts':
            if (count($segments) === 2) {
                // /admin/courts
                switch ($method) {
                    case 'GET':
                        $controller->getAllCourts();
                        break;
                    case 'POST':
                        $controller->createCourt();
                        break;
                    default:
                        methodNotAllowed();
                }
            } elseif (is_numeric($segments[2])) {
                // /admin/courts/{id}
                $courtId = (int)$segments[2];
                
                if (isset($segments[3]) && $segments[3] === 'slots') {
                    // /admin/courts/{id}/slots
                    if ($method === 'POST') {
                        $controller->generateTimeSlots($courtId);
                    } else {
                        methodNotAllowed();
                    }
                } else {
                    // /admin/courts/{id}
                    switch ($method) {
                        case 'PUT':
                            $controller->updateCourt($courtId);
                            break;
                        case 'DELETE':
                            $controller->deleteCourt($courtId);
                            break;
                        default:
                            methodNotAllowed();
                    }
                }
            }
            break;
            
        case 'bookings':
            if (count($segments) === 2) {
                // /admin/bookings
                if ($method === 'GET') {
                    $controller->getAllBookings();
                } else {
                    methodNotAllowed();
                }
            } elseif (isset($segments[2])) {
                if ($segments[2] === 'today') {
                    // /admin/bookings/today
                    if ($method === 'GET') {
                        $controller->getTodaysBookings();
                    } else {
                        methodNotAllowed();
                    }
                } elseif (is_numeric($segments[2])) {
                    // /admin/bookings/{id}
                    $bookingId = (int)$segments[2];
                    if ($method === 'PUT') {
                        $controller->updateBooking($bookingId);
                    } else {
                        methodNotAllowed();
                    }
                }
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Admin endpoint not found']);
    }
}

function methodNotAllowed() {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}