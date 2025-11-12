<?php
/**
 * Simple API Test Script
 * Run this to test if your API endpoints are working
 */

function testAPI($endpoint, $method = 'GET', $data = null) {
    $baseUrl = 'http://localhost/Sports-HUB/backend/public/api';
    $url = $baseUrl . $endpoint;
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10
    ];
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    echo "Testing: $method $endpoint\n";
    echo "HTTP Code: $httpCode\n";
    
    if ($error) {
        echo "Error: $error\n";
    } else {
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
    echo "---\n";
    
    return json_decode($response, true);
}

echo "SportzHub API Test\n";
echo "==================\n\n";

// Test basic endpoints
testAPI('/courts');
testAPI('/courts/types');
testAPI('/courts/locations');

// Test admin login
testAPI('/auth/login', 'POST', [
    'email' => 'admin@test.com',
    'password' => 'admin123'
]);

// Test user registration
testAPI('/auth/register', 'POST', [
    'full_name' => 'Test User',
    'email' => 'test@example.com',
    'phone_number' => '1111111111',
    'password' => 'password123'
]);

echo "API test completed!\n";