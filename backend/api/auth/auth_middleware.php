<?php
function validateToken() {
    // Get the Authorization header
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Check if the Authorization header exists and has the correct format
    if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        return false;
    }
    
    $token = $matches[1];
    
    // Get database connection
    global $conn;
    
    // Query to find user with this token
    $sql = "SELECT id, username, firstname, lastname, email FROM users WHERE token = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        return $user;
    }
    
    return false;
} 