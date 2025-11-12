<?php
require_once __DIR__ . '/../../services/AuthService.php';

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                return;
            }

            $email = $data['email'];
            $password = $data['password'];

            // Check if it's an admin login (email contains 'admin')
            if (strpos(strtolower($email), 'admin') !== false) {
                // Extract username from email (part before @)
                $username = explode('@', $email)[0];
                $result = $this->authService->loginAdmin($username, $password);
            } else {
                $result = $this->authService->loginUser($email, $password);
            }

            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $requiredFields = ['full_name', 'email', 'phone_number', 'password'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field $field is required"]);
                    return;
                }
            }

            $result = $this->authService->registerUser($data);
            http_response_code(201);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createAdmin(): void
    {
        try {
            // This should be protected in production
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['username']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username and password are required']);
                return;
            }

            $admin = $this->authService->createAdmin($data);
            http_response_code(201);
            echo json_encode(['success' => true, 'admin' => $admin->toArray()]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        // For stateless JWT tokens, logout is handled client-side
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }
}