<?php
// Configure error handling for API responses
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error output to response

// Function to handle errors and return them as JSON
function returnError($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json'); // Always return JSON

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if connection file exists
if (!file_exists('../../config/connection.php')) {
    returnError(500, 'Database configuration file not found');
}

// Try to load database connection
try {
    require_once '../../config/connection.php';
    
    // Check if database connection is successful
    if (!$conn || $conn->connect_error) {
        returnError(500, 'Database connection failed: ' . ($conn ? $conn->connect_error : 'Unknown error'));
    }
} catch (Exception $e) {
    returnError(500, 'Database connection error: ' . $e->getMessage());
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
}

// Get JSON data from request body
try {
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        returnError(400, 'Invalid JSON input: ' . json_last_error_msg());
    }
} catch (Exception $e) {
    returnError(400, 'Failed to process request data: ' . $e->getMessage());
}

// Check if this is a login or registration request
$isLogin = !isset($data['email']); // If email is not present, it's a login request

if ($isLogin) {
    // Handle Login
    if (!isset($data['username']) || !isset($data['password'])) {
        returnError(400, 'Username and password are required');
    }

    $username = $conn->real_escape_string($data['username']);
    $password = $data['password'];

    try {
        // Query to check user credentials
        $sql = "SELECT id, username, password, firstname, lastname, email FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            returnError(500, 'Database query preparation failed: ' . $conn->error);
        }
        
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
                        'username' => $user['username'],
                        'firstName' => $user['firstname'],
                        'lastName' => $user['lastname'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                returnError(401, 'Invalid credentials');
            }
        } else {
            returnError(401, 'Invalid credentials');
        }
    } catch (Exception $e) {
        returnError(500, 'Login error: ' . $e->getMessage());
    }
} else {
    // Handle Registration
    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName'])) {
        returnError(400, 'Username, email, password, first name, and last name are required');
    }

    $username = $conn->real_escape_string($data['username']);
    $email = $conn->real_escape_string($data['email']);
    $firstName = $conn->real_escape_string($data['firstName']);
    $lastName = $conn->real_escape_string($data['lastName']);
    $password = $data['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        returnError(400, 'Invalid email format');
    }

    try {
        // Check if username or email already exists
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);
        if (!$checkStmt) {
            returnError(500, 'Database query preparation failed: ' . $conn->error);
        }
        
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            returnError(400, 'Username or email already exists');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generate token
        $token = bin2hex(random_bytes(32));

        // Insert new user
        $insertSql = "INSERT INTO users (username, email, password, firstname, lastname, token) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            returnError(500, 'Database query preparation failed: ' . $conn->error);
        }
        
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
            returnError(500, 'Registration failed: ' . $insertStmt->error);
        }

        $insertStmt->close();
        $checkStmt->close();
    } catch (Exception $e) {
        returnError(500, 'Registration error: ' . $e->getMessage());
    }
}

if (isset($stmt) && $stmt) {
    $stmt->close();
}
if ($conn) {
    $conn->close();
}
?> 