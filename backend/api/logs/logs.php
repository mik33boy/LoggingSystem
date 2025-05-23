<?php
// Suppress errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to handle errors and return them as JSON
function returnError($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

// Check if required files exist
if (!file_exists(__DIR__ . '/../../config/connection.php')) {
    returnError(500, 'Database configuration file not found');
}

if (!file_exists(__DIR__ . '/../auth/auth_middleware.php')) {
    returnError(500, 'Auth middleware file not found');
}

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../auth/auth_middleware.php';

// Get user from token
$user = validateToken();
if (!$user) {
    returnError(401, 'Unauthorized');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname",
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    switch ($method) {
        case 'GET':
            if ($action === 'dashboard') {
                // Get dashboard statistics
                $stats = array();
                
                // Get total logs count
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM logs WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $stats['total_logs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Get logs by direction
                $stmt = $pdo->prepare("SELECT direction, COUNT(*) as count FROM logs WHERE user_id = ? GROUP BY direction");
                $stmt->execute([$user['id']]);
                $stats['by_direction'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get logs by type
                $stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM logs WHERE user_id = ? GROUP BY type");
                $stmt->execute([$user['id']]);
                $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get recent logs
                $stmt = $pdo->prepare("
                    SELECT * FROM logs 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $stmt->execute([$user['id']]);
                $stats['recent_logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $stats]);
            } else {
                // Get all logs for the user, join users for full name, alias created_at as timestamp
                $stmt = $pdo->prepare("
                    SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp
                    FROM logs l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.user_id = ? 
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute([$user['id']]);
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $logs]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                returnError(400, 'Invalid JSON input: ' . json_last_error_msg());
            }
            
            // Validate required fields
            $required_fields = ['direction', 'type', 'subject'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    returnError(400, "Missing required field: $field");
                }
            }
            
            // Insert new log
            $stmt = $pdo->prepare("
                INSERT INTO logs (
                    user_id, direction, type, subject, content, 
                    sender, recipient, confidentiality_level, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $user['id'],
                $data['direction'],
                $data['type'],
                $data['subject'],
                $data['content'] ?? null,
                $data['sender'] ?? null,
                $data['recipient'] ?? null,
                $data['confidential'] ? 'confidential' : 'public'
            ]);
            
            $logId = $pdo->lastInsertId();
            
            // Get the created log, join users for full name, alias created_at as timestamp
            $stmt = $pdo->prepare("
                SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp
                FROM logs l
                JOIN users u ON l.user_id = u.id
                WHERE l.id = ?
            ");
            $stmt->execute([$logId]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $log]);
            break;

        default:
            returnError(405, 'Method not allowed');
    }
} catch (PDOException $e) {
    returnError(500, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    returnError(400, $e->getMessage());
}
