<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 3600"); // Cache preflight response for 1 hour

// Include JWT library
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database configuration (XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'communication_logger');

// JWT configuration
define('JWT_SECRET', 'EGlcf83JFn4LBfXzQOc47lXNK3zxDgOUJcMQRX6kVlXgYRSbOMZZRpz6IQpqLWvr');
define('JWT_ALGORITHM', 'HS256');

// Connect to MySQL database
try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Helper functions
function authenticate() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Authorization header missing']));
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    try {
        return JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
    } catch (Exception $e) {
        http_response_code(401);
        die(json_encode(['error' => 'Invalid token: ' . $e->getMessage()]));
    }
}

function validateInput($data, $fields) {
    $errors = [];
    
    if (!is_array($data)) {
        throw new Exception("Input data must be an array");
    }
    
    foreach ($fields as $field => $rules) {
        if (!isset($data[$field])) {
            $errors[$field] = 'Field is required';
            continue;
        }
        
        if ($rules['type'] === 'string' && !is_string($data[$field])) {
            $errors[$field] = 'Must be a string';
        }
        
        if (isset($rules['min']) && strlen($data[$field]) < $rules['min']) {
            $errors[$field] = "Must be at least {$rules['min']} characters";
        }
        
        if (isset($rules['max']) && strlen($data[$field]) > $rules['max']) {
            $errors[$field] = "Must be at most {$rules['max']} characters";
        }
    }
    
    if (!empty($errors)) {
        error_log("Validation errors: " . json_encode($errors));
        http_response_code(400);
        die(json_encode(['errors' => $errors]));
    }
}
?>