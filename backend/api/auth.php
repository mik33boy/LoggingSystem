<?php
require_once '../config.php';

// Include the namespace at the top of the file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// OPTIONS request is already handled in config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a logout request
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['action']) && $data['action'] === 'logout') {
        // Server-side logout - can be used to invalidate tokens if implementing a token blacklist
        echo json_encode(['message' => 'Logged out successfully']);
        exit;
    }
    
    // Get raw input and log it
    $raw_input = file_get_contents('php://input');
    error_log("Auth request raw input: " . $raw_input);
    
    // Try to decode JSON
    $data = json_decode($raw_input, true);
    
    // Check if JSON was valid
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid JSON input: ' . json_last_error_msg(),
            'raw_input' => $raw_input
        ]);
        exit;
    }
    
    // Validate input
    try {
        validateInput($data, [
            'username' => ['type' => 'string', 'min' => 3, 'max' => 50],
            'password' => ['type' => 'string', 'min' => 6, 'max' => 100]
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Validation error: ' . $e->getMessage()]);
        exit;
    }
    
    // Check user credentials
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($data['password'], $user['password'])) {
        // Create JWT token
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'exp' => time() + 3600 // 1 hour expiration
        ];
        
        $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
        
        // Record login time (you could add this to a login_logs table)
        
        echo json_encode([
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
    } else {
        error_log("Auth failed for user: " . ($data['username'] ?? 'unknown'));
        if ($user) {
            error_log("Password verification failed");
        } else {
            error_log("User not found");
        }
        
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>