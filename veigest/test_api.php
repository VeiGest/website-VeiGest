<?php
/**
 * VeiGest API Test Script (PHP)
 * Tests authentication and basic endpoints
 */

$baseUrl = 'http://localhost:21080/api/v1';

echo "======================================\n";
echo "VeiGest API - Test Script (PHP)\n";
echo "======================================\n\n";

// Test 1: Login
echo "1. Testing Login Endpoint...\n";
$loginResponse = apiRequest('POST', "$baseUrl/auth/login", [
    'username' => 'admin',
    'password' => 'admin'
]);

if (!$loginResponse || !isset($loginResponse['data']['token'])) {
    echo "❌ Login failed! No token received.\n";
    print_r($loginResponse);
    exit(1);
}

$token = $loginResponse['data']['token'];
echo "✓ Login successful! Token received.\n";
echo "Token (first 50 chars): " . substr($token, 0, 50) . "...\n";
echo "User: " . $loginResponse['data']['user']['name'] . "\n";
echo "Company: " . $loginResponse['data']['company']['name'] . " (Code: " . $loginResponse['data']['company']['code'] . ")\n";
echo "Roles: " . implode(', ', $loginResponse['data']['roles']) . "\n\n";

// Decode token to show contents
$tokenData = json_decode(base64_decode($token), true);
echo "Decoded Token Data:\n";
print_r($tokenData);
echo "\n";

// Test 2: Get current user info
echo "2. Testing /auth/me Endpoint...\n";
$meResponse = apiRequest('GET', "$baseUrl/auth/me", null, $token);
echo "User Info: " . ($meResponse['success'] ? '✓' : '❌') . "\n";
if ($meResponse['success']) {
    echo "  - Name: " . $meResponse['data']['user']['name'] . "\n";
    echo "  - Email: " . $meResponse['data']['user']['email'] . "\n";
    echo "  - Company Code: " . $meResponse['data']['company']['code'] . "\n";
}
echo "\n";

// Test 3: Get vehicles
echo "3. Testing /vehicles Endpoint...\n";
$vehiclesResponse = apiRequest('GET', "$baseUrl/vehicles", null, $token);
if (isset($vehiclesResponse['id'])) {
    // Single vehicle response
    echo "✓ Vehicles endpoint working\n";
    echo "  - Found vehicle: " . $vehiclesResponse['license_plate'] . "\n";
} elseif (is_array($vehiclesResponse)) {
    echo "✓ Vehicles endpoint working\n";
    echo "  - Found " . count($vehiclesResponse) . " vehicles\n";
} else {
    echo "Response:\n";
    print_r($vehiclesResponse);
}
echo "\n";

// Test 4: Get users
echo "4. Testing /users Endpoint...\n";
$usersResponse = apiRequest('GET', "$baseUrl/users", null, $token);
if (is_array($usersResponse)) {
    echo "✓ Users endpoint working\n";
    echo "  - Found " . count($usersResponse) . " users\n";
} else {
    echo "Response:\n";
    print_r($usersResponse);
}
echo "\n";

// Test 5: Token refresh
echo "5. Testing /auth/refresh Endpoint...\n";
$refreshResponse = apiRequest('POST', "$baseUrl/auth/refresh", null, $token);
if ($refreshResponse['success'] ?? false) {
    echo "✓ Token refreshed successfully\n";
    echo "  - New expiration: " . date('Y-m-d H:i:s', $refreshResponse['data']['expires_at']) . "\n";
} else {
    echo "Response:\n";
    print_r($refreshResponse);
}
echo "\n";

// Test 6: Logout
echo "6. Testing /auth/logout Endpoint...\n";
$logoutResponse = apiRequest('POST', "$baseUrl/auth/logout", null, $token);
echo "Logout: " . ($logoutResponse['success'] ? '✓' : '❌') . "\n";
echo "  - Message: " . ($logoutResponse['message'] ?? 'No message') . "\n\n";

echo "======================================\n";
echo "✓ All tests completed!\n";
echo "======================================\n";

/**
 * Make an API request
 */
function apiRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    if ($httpCode >= 400) {
        echo "⚠ HTTP $httpCode\n";
    }
    
    return $decoded ?: $response;
}
