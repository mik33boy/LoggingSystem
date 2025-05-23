<?php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function returnError($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/auth/auth_middleware.php';

$user = validateToken();
if (!$user) {
    returnError(401, 'Unauthorized');
}

try {
    // Fetch user info for dashboard (customize as needed)
    $userInfo = [
        'id' => $user['id'],
        'username' => $user['username'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'role' => $user['role'] ?? ''
    ];
    echo json_encode(['success' => true, 'user' => $userInfo]);
} catch (Exception $e) {
    returnError(500, 'Failed to fetch dashboard: ' . $e->getMessage());
} 