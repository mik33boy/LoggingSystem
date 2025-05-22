<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Check if connection file exists
if (!file_exists('../../config/connection.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'Database configuration file not found']);
    exit();
}

require_once '../../config/connection.php';

// Check if database connection is successful
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

// Check if this is a login or registration request
$isLogin = !isset($data['email']); // If email is not present, it's a login request

if ($isLogin) {
    // Handle Login
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit();
    }

    $username = $conn->real_escape_string($data['username']);
    $password = $data['password'];

    // Query to check user credentials
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Generate a token
            $token = bin2hex(random_bytes(32));
            
            // Store token in database
            $updateToken = "UPDATE users SET token = ? WHERE id = ?";
            $stmt = $conn->prepare($updateToken);
            $stmt->bind_param("si", $token, $user['id']);
            $stmt->execute();
            
            // Return success response with token
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
} else {
    // Handle Registration
    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username, email, password, first name, and last name are required']);
        exit();
    }

    $username = $conn->real_escape_string($data['username']);
    $email = $conn->real_escape_string($data['email']);
    $firstName = $conn->real_escape_string($data['firstName']);
    $lastName = $conn->real_escape_string($data['lastName']);
    $password = $data['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit();
    }

    // Check if username or email already exists
    $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username or email already exists']);
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate token
    $token = bin2hex(random_bytes(32));

    // Insert new user
    $insertSql = "INSERT INTO users (username, email, password, firstname, lastname, token) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ssssss", $username, $email, $hashedPassword, $firstName, $lastName, $token);

    if ($insertStmt->execute()) {
        $userId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed']);
    }

    $insertStmt->close();
    $checkStmt->close();
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 