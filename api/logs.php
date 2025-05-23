<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

require_once 'config.php';
require_once 'auth_middleware.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get user from token
$user = validateToken();
if (!$user) {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(401);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_pass,
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
                // Get all logs for the user
                $stmt = $pdo->prepare("
                    SELECT * FROM logs 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$user['id']]);
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $logs]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required_fields = ['direction', 'type', 'subject'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            // Insert new log
            $stmt = $pdo->prepare("
                INSERT INTO logs (
                    user_id, direction, type, subject, content, 
                    sender, recipient, confidentiality_level
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?
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
            
            // Get the created log
            $stmt = $pdo->prepare("SELECT * FROM logs WHERE id = ?");
            $stmt->execute([$logId]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $log]);
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 