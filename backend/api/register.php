<?php
/**
 * User Registration API Endpoint
 * 
 * Handles new user registration requests
 * Validates input, checks for existing users, and creates new accounts
 */
require_once '../config.php';

// OPTIONS request is already handled in config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw input and log it for debugging
    $raw_input = file_get_contents('php://input');
    error_log("Register request raw input: " . $raw_input);
    
    // Try to decode JSON from request body
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
    
    // Validate required fields and field formats
    try {
        validateInput($data, [
            'username' => ['type' => 'string', 'min' => 3, 'max' => 50],
            'email' => ['type' => 'string', 'min' => 5, 'max' => 100],
            'password' => ['type' => 'string', 'min' => 6, 'max' => 100]
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Validation error: ' . $e->getMessage()]);
        exit;
    }
    
    // Check if username already exists in database
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username already exists']);
        exit;
    }
    
    // Hash the password for security
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    try {
        // Insert the new user into database
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
        $stmt->execute([$data['username'], $hashedPassword]);
        
        $user_id = $db->lastInsertId();
        
        // Return success response
        http_response_code(201);
        echo json_encode([
            'id' => $user_id,
            'message' => 'User registered successfully'
        ]);
    } catch (PDOException $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Return error for unsupported HTTP methods
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?> 