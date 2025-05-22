<?php
require_once '../config.php';

// Replace wildcard with specific origin for credentials requests
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:1420");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Log headers for debugging
    error_log("Authentication request headers: " . json_encode(getallheaders()));
    
    // Verify JWT token and get user info
    try {
        $user = authenticate();
        
        // Get user details from database to ensure we have the latest data
        $stmt = $db->prepare("SELECT id, username, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$user->user_id]);
        $userData = $stmt->fetch();
        
        if (!$userData) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'user' => $userData
        ]);
    } catch (Exception $e) {
        error_log("User API error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error retrieving user data: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?> 